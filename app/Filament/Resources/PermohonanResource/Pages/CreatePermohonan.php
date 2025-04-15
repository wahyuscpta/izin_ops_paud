<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($this->isKirimPermohonan) {
            $data['status_permohonan'] = 'menunggu_verifikasi';
            $data['tgl_permohonan'] = now();
        } else {
            $data['status_permohonan'] = 'draft';
        }
        
        $data['no_permohonan'] = 'IZIN-' . now()->format('YmdHis') . '-' . rand(1000, 9999);
        $data['user_id'] = Auth::id();
        $data['tgl_permohonan'] = now();
        $data['tgl_status_terakhir'] = now();
        
        return $data;
    }    

    protected function afterCreate(): void
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

            if (!empty($field) && !empty($filePath)) {
                $lampiranData[] = [
                    'lampiran_type' => $field,
                    'lampiran_path' => is_array($filePath) ? reset($filePath) : $filePath,
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

    protected function getCreateInDraftFormAction(): Action
    {
        return Action::make('draft')
            ->label('Simpan Draft')
            ->color('gray')
            ->action(function(){
                $this->isKirimPermohonan = false;
                $this->create();
            });
    }
}
