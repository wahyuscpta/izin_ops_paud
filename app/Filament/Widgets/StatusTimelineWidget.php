<?php

namespace App\Filament\Widgets;

use App\Models\Permohonan;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class StatusTimelineWidget extends Widget 
{
    protected static string $view = 'filament.widgets.status-timeline-widget';

    public static function canView(): bool
    {
        return Auth::user()->hasRole(['pemohon']);
    }

    protected function getViewData(): array
    {
        $permohonanTerakhir = Permohonan::where('user_id', Auth::id())
            ->latest()
            ->first();         

        $status = $permohonanTerakhir?->status_permohonan;
        
        // Tambahkan tanggal untuk setiap status yang sudah dilalui
        $statusDates = [
            'draft' => $permohonanTerakhir?->tgl_permohonan,
            'menunggu_verifikasi' => $permohonanTerakhir?->tgl_verifikasi_berkas,
            'menunggu_validasi_lapangan' => $permohonanTerakhir?->tgl_validasi_lapangan,
            'proses_penerbitan_izin' => $permohonanTerakhir?->tgl_proses_penerbitan_izin,
            'izin_diterbitkan' => $permohonanTerakhir?->tgl_izin_terbit,
            'permohonan_ditolak' => $permohonanTerakhir?->tgl_ditolak,
        ];

        return [
            'currentStatus' => $status,
            'permohonan' => $permohonanTerakhir,
            'statusDates' => $statusDates,
        ];
    }
}