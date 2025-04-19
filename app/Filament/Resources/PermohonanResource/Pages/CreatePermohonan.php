<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use League\Flysystem\FilesystemException;

class CreatePermohonan extends CreateRecord
{
    protected static string $resource = PermohonanResource::class;

    protected static ?string $breadcrumb = 'Pengajuan Baru';

    protected static ?string $title = 'Formulir Permohonan';

    protected static bool $canCreateAnother = false;

    public bool $isKirimPermohonan = false;

    /**
     * Get redirect URL after record creation
     */
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    /**
     * Prepare record data before creation
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Authorize user to create this resource
        Gate::authorize('create', static::getModel());
        
        $data['status_permohonan'] = $this->isKirimPermohonan ? 'menunggu_verifikasi' : 'draft';
        $data['no_permohonan'] = 'IZIN-' . now()->format('YmdHis') . '-' . rand(1000, 9999);
        $data['user_id'] = Auth::id();
        $data['tgl_permohonan'] = now();
        $data['tgl_status_terakhir'] = now();

        return $data;
    }  

    /**
     * Handle additional processing after create
     */
    protected function afterCreate(): void
    {
        DB::beginTransaction();
        
        try {
            $this->createLampiran($this->record);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            report($e);
            $this->cleanupFailedRecord();
            $this->showErrorNotification('Terjadi kesalahan pada database. Silakan coba lagi nanti.');
        } catch (FilesystemException $e) {
            DB::rollBack();
            report($e);
            $this->cleanupFailedRecord();
            $this->showErrorNotification('Terjadi kesalahan saat menyimpan berkas. Pastikan berkas memiliki format yang benar.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            $this->cleanupFailedRecord();
            $this->showErrorNotification('Terjadi kesalahan saat menyimpan permohonan. Silakan periksa data Anda dan coba lagi.');
        }
    }

    /**
     * Create attachment records for the application
     */
    protected function createLampiran($permohonan): void
    {
        $requiredFields = [
            'ktp_ketua', 'struktur_yayasan', 'ijasah_penyelenggara', 'ijasah_kepsek', 'ijasah_pendidik',
            'sarana_prasarana', 'kurikulum', 'tata_tertib', 'peta_lokasi', 'daftar_peserta', 'daftar_guru',
            'akte_notaris', 'rek_ke_lurah', 'rek_dari_lurah', 'rek_ke_korwil', 'rek_dari_korwil',
            'permohonan_izin', 'rip', 'imb', 'perjanjian_sewa', 'nib',
        ];

        // Prepare data for batch insert to improve performance
        $lampiranBatch = [];

        foreach ($requiredFields as $field) {
            $filePath = data_get($this->data, $field);
            
            if (!empty($filePath)) {
                $lampiranBatch[] = [
                    'permohonan_id' => $permohonan->id,
                    'lampiran_type' => $field,
                    'lampiran_path' => is_array($filePath) ? reset($filePath) : $filePath,
                ];
            }
        }

        // Perform batch insert if we have attachments to add
        if (!empty($lampiranBatch)) {
            $permohonan->lampiran()->createMany($lampiranBatch);
        }
    }

    /**
     * Customize notification message based on submission type
     */
    protected function getCreatedNotificationMessage(): ?string
    {
        return $this->isKirimPermohonan
            ? 'Permohonan berhasil dikirim dan sedang menunggu verifikasi.'
            : 'Permohonan berhasil disimpan sebagai draft.';
    }
    
    /**
     * Add additional notification customization if needed
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil Disimpan!')
            ->body($this->getCreatedNotificationMessage())
            ->duration(5000);
    }
    
    /**
     * Clean up record if creation process failed
     */
    protected function cleanupFailedRecord(): void
    {
        if ($this->record && $this->record->exists) {
            try {
                $this->record->delete();
            } catch (\Exception $e) {
                report($e);
            }
        }
    }
    
    /**
     * Handle validation errors
     */
    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title('Data Tidak Valid')
            ->body('Harap periksa kembali data yang Anda masukkan.')
            ->danger()
            ->duration(8000)
            ->send();
            
        parent::onValidationError($exception);
    }

    /**
     * Display error notification with actions
     */
    protected function showErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Gagal Disimpan!')
            ->body($message)
            ->danger()
            ->duration(8000)
            ->persistent()
            ->send();
    }

    /**
     * Define custom form actions
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateInDraftFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    /**
     * Create action for saving as draft
     */
    protected function getCreateInDraftFormAction(): Action
    {
        return Action::make('draft')
            ->label('Simpan Draft')
            ->color('gray')
            ->action(function() {
                $this->isKirimPermohonan = false;
                $this->create();
            });
    }    
}