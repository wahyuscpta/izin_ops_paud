<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatePermohonan extends CreateRecord
{
    protected static string $resource = PermohonanResource::class;

    protected static ?string $breadcrumb = 'Pengajuan Baru';

    protected static ?string $title = 'Formulir Permohonan';

    protected static bool $canCreateAnother = false;

    public bool $isKirimPermohonan = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Memodifikasi data input sebelum proses penyimpanan dilakukan
    protected function mutateFormDataBeforeCreate(array $data): array
    {                
        $data['status_permohonan'] = $this->isKirimPermohonan ? 'menunggu_verifikasi' : 'draft';
        $data['user_id'] = Auth::id();
        $data['tgl_status_terakhir'] = now();
        $data['tgl_permohonan'] = now();
    
        return $data;
    }

    // Override method create untuk handle draft dan kirim permohonan
    public function create(?bool $shouldValidateForms = null): void
    {
        // Jika kirim permohonan maka akan validasi form
        $shouldValidateForms = $shouldValidateForms ?? $this->isKirimPermohonan;
    
        try {
            // getState -> data tervalidasi
            // getRawState -> data tanpa validasi
            $data = $shouldValidateForms ? $this->form->getState() : $this->form->getRawState();
            
            $data = $this->mutateFormDataBeforeCreate($data);
            
            // MULAI TRANSAKSI
            DB::beginTransaction();
            
            try {
                // Buat record
                $record = $this->handleRecordCreation($data);
                $this->record = $record;
                
                // Proses lampiran
                $this->createLampiran($record, $data);            
                
                // Commit transaksi
                DB::commit();
                
                // Notifikasi sukses
                $this->showSuccessNotification();
                
                // Save relationships
                $this->form->model($record)->saveRelationships();

                // Proses after create
                $this->afterCreate(); 
    
                if ($redirectUrl = $this->getRedirectUrl()) {
                    $this->redirect($redirectUrl);
                }
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error creating permohonan: ' . $e->getMessage());
                $this->showErrorNotification();
                throw $e;
            }
            
        } catch (Halt $exception) {
            return;
        }
    }

    protected function afterCreate(): void
    {
        // Log aktivitas
        $this->logActivity($this->record);
    }
    
    protected function createLampiran($permohonan): void
    {
        $requiredFields = [
            'ktp_ketua', 'struktur_yayasan', 'ijasah_penyelenggara', 'ijasah_kepsek', 
            'ijasah_pendidik', 'sarana_prasarana', 'kurikulum', 'tata_tertib', 
            'peta_lokasi', 'daftar_peserta', 'daftar_guru', 'akte_notaris', 
            'rek_ke_lurah', 'rek_dari_lurah', 'rek_ke_korwil', 'rek_dari_korwil', 
            'permohonan_izin', 'rip', 'imb', 'perjanjian_sewa', 'nib'
        ];
    
        $lampiranData = [];

        // Ambil data dari form state
        $formData = $this->form->getState();
    
        foreach ($requiredFields as $field) {
            // Ambil isi file yang diupload user dari form berdasarkan nama field-nya
            $filePath = data_get($formData, $field);
            
            if (!empty($filePath)) {
                $lampiranData[] = [
                    'permohonan_id' => $permohonan->id,
                    'lampiran_type' => $field,
                    // Cek apakah filePath berbentuk array, ambil yang pertama, jika tidak masukkan string filePath
                    'lampiran_path' => is_array($filePath) ? reset($filePath) : $filePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
    
        if (!empty($lampiranData)) {
            $permohonan->lampiran()->createMany($lampiranData);
        }
    }
    
    protected function getFormActions(): array
    {
        return [
            $this->getCreateInDraftFormAction(),
            $this->getCancelFormAction(),
        ];
    }
    
    // Aksi untuk tombol draft
    protected function getCreateInDraftFormAction(): Action
    {
        return Action::make('draft')
            ->label('Simpan Draft')
            ->color('gray')
            ->action(function() {
                $this->isKirimPermohonan = false; // Set status draft
                $this->create(shouldValidateForms: false); // Jalankan proses create tanpa validasi
            });
    }
    
    // Method untuk logging aktivitas
    protected function logActivity(): void
    {
        // Refresh record dengan relasi
        $this->record = $this->record->fresh(['identitas']);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($this->record)
            ->withProperties([
                'attributes' => [
                    'status_permohonan' => $this->record->status_permohonan,
                    'nama_pemohon' => $this->record->nama_pemohon,
                ],
                'role' => Auth::user()?->getRoleNames()?->first(),
            ])
            ->event('created')
            ->useLog('Permohonan') 
            ->log('Telah mengajukan permohonan izin operasional untuk lembaga ' . $this->record->identitas->nama_lembaga . '.');
    }
    
    // Bersihkan data permohonan kalau terjadi error
    protected function cleanupFailedRecord(): void
    {
        if ($this->record && $this->record->exists) {
            try {
                // Hapus lampiran terkait jika ada
                if ($this->record->lampiran()) {
                    $this->record->lampiran()->delete();
                }
                $this->record->delete();
            } catch (\Exception $e) {
                report($e);
            }
        }
    }
    
    protected function showSuccessNotification(): void
    {
        Notification::make()
            ->success()
            ->title('Berhasil!')
            ->body($this->isKirimPermohonan ? 
                'Permohonan berhasil dikirim.' : 
                'Permohonan berhasil disimpan sebagai draft.')
            ->send();
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return null; // Menonaktifkan notifikasi default dari CreateRecord
    }
    
    protected function showErrorNotification(): void
    {
        Notification::make()
            ->danger()
            ->title('Gagal!')
            ->body('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.')
            ->send();
    }
}