<?php

namespace App\Filament\Widgets;

use App\Models\Permohonan;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1; 

    public static function canView(): bool
    {
        return Auth::user()->hasRole(['admin', 'kepala_dinas', 'super_admin']);
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Permohonan Masuk', Permohonan::count())
                ->description('Total semua permohonan')
                ->descriptionIcon('heroicon-o-document-text', IconPosition::Before)
                ->chart([1, 3, 5, 10, 20, 40])
                ->color('primary'),

            Stat::make('Permohonan Dalam Proses', Permohonan::whereIn('status_permohonan', ['menunggu_verifikasi', 'menunggu_validasi_lapangan'])->count())
                ->description('Dalam tahap proses')
                ->descriptionIcon('heroicon-o-clock', IconPosition::Before)
                ->chart([1, 3, 5, 10, 20, 40])
                ->color('warning'),

            Stat::make('Permohonan Disetujui', Permohonan::whereStatusPermohonan('disetujui')->count())
                ->description('Permohonan yang disetujui')
                ->descriptionIcon('heroicon-o-check-circle', IconPosition::Before)
                ->chart([1, 3, 5, 10, 20, 40])
                ->color('success'),

            Stat::make('Permohonan Ditolak', Permohonan::whereStatusPermohonan('ditolak')->count())
                ->description('Permohonan yang ditolak')
                ->descriptionIcon('heroicon-o-x-circle', IconPosition::Before)
                ->chart([1, 3, 5, 10, 20, 40])
                ->color('danger'),
        ];
    }
}
