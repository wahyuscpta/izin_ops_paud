<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EditPermohonan extends EditRecord
{
    protected static string $resource = PermohonanResource::class;

    protected static ?string $breadcrumb = 'Edit Permohonan';

    protected static ?string $title = 'Perbarui Data Permohonan';

    public bool $isKirimPermohonan = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Persiapan data sebelum disimpan ke database
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['tgl_status_terakhir'] = now();    
        $data['status_permohonan'] = $this->isKirimPermohonan ? 'menunggu_verifikasi' : 'draft';
        
        if ($this->isKirimPermohonan) {
            $data['tgl_permohonan'] = now();
        }
    
        return $data;
    }

    // Override afterSave untuk handle lampiran dan activity log
    protected function afterSave(): void
    {
        // MULAI TRANSAKSI
        DB::beginTransaction();
        
        try {
            // Update lampiran
            $this->updateLampiran($this->record);
            
            // Log aktivitas
            $this->logActivity($this->record);
            
            // Commit transaksi
            DB::commit();
            
            // Notifikasi sukses
            $this->showSuccessNotification();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorNotification();
            throw $e;
        }
    }

    // Method untuk handle draft dan kirim permohonan
    protected function handleSaveAction(?bool $shouldValidateForms = null): void
    {
        $shouldValidateForms = $shouldValidateForms ?? $this->isKirimPermohonan;
        
        try {
            if ($shouldValidateForms) {
                $this->form->validate();
            }
            
            $this->save();
            
        } catch (Halt $exception) {
            return;
        }
    } 

    protected function updateLampiran($permohonan): void
    {
        $requiredFields = [
            'ktp_ketua', 'struktur_yayasan', 'ijasah_penyelenggara', 'ijasah_kepsek', 'ijasah_pendidik', 'sarana_prasarana', 'kurikulum', 'tata_tertib', 'peta_lokasi', 'daftar_peserta', 'daftar_guru', 'akte_notaris', 'rek_ke_lurah', 'rek_dari_lurah', 'rek_ke_korwil', 'rek_dari_korwil', 'permohonan_izin', 'rip', 'imb', 'perjanjian_sewa', 'nib'
        ];
    
        $lampiranData = [];
    
        foreach ($requiredFields as $field) {
            // Ambil isi file yang diupload user dari form berdasarkan nama field-nya
            $filePath = data_get($this->data, $field);
    
            if (!empty($filePath)) {
                // Cari lampiran lama berdasarkan jenis lampiran (jika sudah ada di DB)
                $lampiran = $permohonan->lampiran()->where('lampiran_type', $field)->first();
    
                // Jika lampiran lama ada dan ada path filenya, hapus file lama dari storage
                if ($lampiran && $lampiran->lampiran_path) {
                    Storage::disk('public')->delete($lampiran->lampiran_path);
                }
                
                if ($lampiran) {
                    // Jika lampiran sudah ada, update path-nya dengan file baru
                    $lampiran->update([
                        'lampiran_path' => is_array($filePath) ? reset($filePath) : $filePath,
                    ]);
                } else {
                     // Jika belum ada lampiran, siapkan untuk disimpan baru
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

    // Method untuk logging aktivitas
    protected function logActivity(): void
    {
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
            ->log('Telah mengajukan permohonan izin operasional untuk lembaga' . $this->record->identitas->nama_lembaga . '');
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

    protected function showSuccessNotification(): void
    {
        Notification::make()
            ->success()
            ->title('Berhasil!')
            ->body($this->isKirimPermohonan ? 
                'Permohonan berhasil diperbarui dan dikirim.' : 
                'Draft permohonan berhasil diperbarui.')
            ->send();
    }

    protected function showErrorNotification(): void
    {
        Notification::make()
            ->danger()
            ->title('Gagal!')
            ->body('Terjadi kesalahan saat memperbarui data. Silakan coba lagi.')
            ->send();
    }

    protected function getSavedNotification(): ?Notification
    {
        return null; // Menonaktifkan notifikasi default dari CreateRecord
    }

    protected function canEdit(): bool
    {
        return in_array($this->record->status_permohonan, ['draft', 'ditolak']);
    }
}