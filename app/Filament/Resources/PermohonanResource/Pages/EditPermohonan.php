<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditPermohonan extends EditRecord
{
    protected static string $resource = PermohonanResource::class;

    protected static ?string $breadcrumb = 'Edit Permohonan';

    protected static ?string $title = 'Perbarui Data Permohonan';

    public bool $isKirimPermohonan = false;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {

        $data['tgl_status_terakhir'] = now();

        if ($this->isKirimPermohonan) {
            $data['status_permohonan'] = 'menunggu_verifikasi';
            $data['tgl_permohonan'] = now();
        } else {
            $data['status_permohonan'] = 'draft';
        }        

        return $data;
    }

    protected function afterSave(): void
    {
        $permohonan = $this->record;

        $requiredFields = [
            'ktp_ketua',
            'struktur_yayasan',
            'ijasah_penyelenggara',
            'ijasah_kepsek',
            'ijasah_pendidik',
            'sarana_prasarana',
            'kurikulum',
            'tata_tertib',
            'peta_lokasi',
            'daftar_peserta',
            'daftar_guru',
            'akte_notaris',
            'rek_ke_lurah',
            'rek_dari_lurah',
            'rek_ke_korwil',
            'rek_dari_korwil',
            'permohonan_izin',
            'rip',
            'imb',
            'perjanjian_sewa',
            'nib',
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
                $this->save(); // Memastikan bahwa data disimpan setelah tombol ditekan
            });
    }
}
