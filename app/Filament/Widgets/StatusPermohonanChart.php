<?php

namespace App\Filament\Widgets;

use App\Models\Permohonan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class StatusPermohonanChart extends ChartWidget
{
    protected static ?string $heading = 'Status Permohonan';
    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '255px';
    
    public static function canView(): bool
    {
        return Auth::user()->hasRole(['admin', 'kepala_dinas', 'super_admin']);
    }
    
    protected function getData(): array
    {
        $statuses = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'menunggu_validasi_lapangan' => 'Menunggu Validasi',
            'izin_diterbitkan' => 'Disetujui',
            'permohonan_ditolak' => 'Ditolak'
        ];
        
        $data = [];
        $labels = [];
        $colors = ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b'];
        $backgroundColor = [];
        
        $i = 0;
        foreach ($statuses as $key => $label) {
            $count = Permohonan::whereStatusPermohonan($key)->count();
            $data[] = $count;
            $labels[] = "$label ($count)"; // Tambahkan jumlah pada label
            $backgroundColor[] = $colors[$i];
            $i++;
        }
        
        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const data = context.chart.data.datasets[0].data;
                            const total = data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ": " + value + " permohonan (" + percentage + "%)";
                        }'
                    ]
                ],
                'legend' => [
                    'position' => 'bottom',
                ]
            ],
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
        ];
    }
    
    protected function getType(): string
    {
        return 'pie';
    }
}
