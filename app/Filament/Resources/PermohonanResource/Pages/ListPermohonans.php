<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use App\Models\Permohonan;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;

class ListPermohonans extends ListRecords
{
    protected static string $resource = PermohonanResource::class;

    protected static ?string $title = 'Permohonan';

    protected static ?string $breadcrumb = 'Daftar Permohonan';

    public Collection $orderbyStatuses;

    public function getTotalPermohonanCount($status = null)
    {
        $query = Permohonan::query();
        
        if ($status) {
            $query->where('status_permohonan', $status);
        }

        return $query->count();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Permohonan Baru')
            ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Semua' => Tab::make('Semua')
                ->badge(function () {
                    return $this->getTotalPermohonanCount();
                }),

            'Menunggu Verifikasi' => Tab::make('Menunggu Verifikasi')
                ->badge(function () {
                    return $this->getTotalPermohonanCount('menunggu_verifikasi');
                })
                ->modifyQueryUsing(fn ($query) => $query->where('status_permohonan', 'menunggu_verifikasi')),

            'Proses Validasi Lapangan' => Tab::make('Validasi Lapangan')
                ->badge(function () {
                    return $this->getTotalPermohonanCount('menunggu_validasi_lapangan');
                })
                ->modifyQueryUsing(fn ($query) => $query->where('status_permohonan', 'menunggu_validasi_lapangan')),

            'Proses Penerbitan Izin' => Tab::make('Penerbitan Izin')
                ->badge(function () {
                    return $this->getTotalPermohonanCount('proses_penerbitan_izin');
                })
                ->modifyQueryUsing(fn ($query) => $query->where('status_permohonan', 'proses_penerbitan_izin')),

            'Izin Diterbitkan' => Tab::make('Izin Diterbitkan')
                ->badge(function () {
                    return $this->getTotalPermohonanCount('izin_diterbitkan');
                })
                ->modifyQueryUsing(fn ($query) => $query->where('status_permohonan', 'izin_diterbitkan')),
                
            'Permohonan Ditolak' => Tab::make('Permohonan Ditolak')
                ->badge(function () {
                    return $this->getTotalPermohonanCount('permohonan_ditolak');
                })
                ->modifyQueryUsing(fn ($query) => $query->where('status_permohonan', 'permohonan_ditolak')),
        ];
    }

}
