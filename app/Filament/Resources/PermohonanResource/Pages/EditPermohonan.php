<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use League\Flysystem\FilesystemException;

class EditPermohonan extends EditRecord
{
    protected static string $resource = PermohonanResource::class;

    protected static ?string $breadcrumb = 'Edit Permohonan';

    protected static ?string $title = 'Perbarui Data Permohonan';

    public bool $isKirimPermohonan = false;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Prepare record before data saving
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['tgl_status_terakhir'] = now();        
        $data['status_permohonan'] = $this->isKirimPermohonan ? 'menunggu_verifikasi' : 'draft';    
        if ($this->isKirimPermohonan) {
            $data['tgl_permohonan'] = now();
        }

        return $data;
    }

    /**
     * Handle additional processing after save
     */
    protected function afterSave(): void
    {
        DB::beginTransaction();
        
        try {
            $this->updateLampiran($this->record);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            report($e);
            $this->showErrorNotification('Terjadi kesalahan pada database. Silakan coba lagi nanti.');
        } catch (FilesystemException $e) {
            DB::rollBack();
            report($e);
            $this->showErrorNotification('Terjadi kesalahan saat menyimpan berkas. Pastikan berkas memiliki format yang benar.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            $this->showErrorNotification('Terjadi kesalahan saat memperbarui permohonan. Silakan periksa data Anda dan coba lagi.');
        }
    }

    /**
     * Update attachment records for the application
     */
    protected function updateLampiran($permohonan): void
    {
        $requiredFields = [
            'ktp_ketua', 'struktur_yayasan', 'ijasah_penyelenggara', 'ijasah_kepsek', 'ijasah_pendidik',
            'sarana_prasarana', 'kurikulum', 'tata_tertib', 'peta_lokasi', 'daftar_peserta', 'daftar_guru',
            'akte_notaris', 'rek_ke_lurah', 'rek_dari_lurah', 'rek_ke_korwil', 'rek_dari_korwil',
            'permohonan_izin', 'rip', 'imb', 'perjanjian_sewa', 'nib',
        ];

        $lampiranData = [];

        foreach ($requiredFields as $field) {
            $filePath = data_get($this->data, $field);

            if (!empty($filePath)) {
                $lampiran = $permohonan->lampiran()->where('lampiran_type', $field)->first();

                if ($lampiran && $lampiran->lampiran_path) {
                    Storage::disk('public')->delete($lampiran->lampiran_path);
                }
                
                if ($lampiran) {
                    $lampiran->update([
                        'lampiran_path' => is_array($filePath) ? reset($filePath) : $filePath,
                    ]);
                } else {
                    $lampiranData[] = [
                        'lampiran_type' => $field,
                        'lampiran_path' => is_array($filePath) ? reset($filePath) : $filePath,
                    ];
                }
            }
        }

        if (!empty($lampiranData)) {
            $permohonan->lampiran()->createMany($lampiranData);
        }
    }

    /**
     * Customize notification message based on submission type
     */
    protected function getSavedNotificationMessage(): ?string
    {
        return $this->isKirimPermohonan
            ? 'Permohonan berhasil diperbarui dan dikirim untuk verifikasi.'
            : 'Permohonan berhasil diperbarui dan disimpan sebagai draft.';
    }
    
    /**
     * Add additional notification customization if needed
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil Diperbarui!')
            ->body($this->getSavedNotificationMessage())
            ->duration(5000);
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
            ->title('Gagal Diperbarui!')
            ->body($message)
            ->danger()
            ->duration(8000)
            ->persistent()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getUpdateInDraftFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getUpdateInDraftFormAction(): Action
    {
        return Action::make('draft')
            ->label('Update Draft')
            ->color('gray')
            ->action(function () {
                $this->isKirimPermohonan = false;
                $this->save();
            });
    }
}