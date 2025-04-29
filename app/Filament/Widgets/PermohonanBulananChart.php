<?php

namespace App\Filament\Widgets;

use App\Models\Permohonan;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PermohonanBulananChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Permohonan Bulanan';
    protected static ?string $description = 'Jumlah permohonan izin operasional setiap bulan';
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return Auth::user()->hasRole(['admin', 'kepala_dinas', 'super_admin']);
    }

    protected function getFilters(): ?array
    {
        return [
            '3_bulan' => '3 Bulan Terakhir',
            '6_bulan' => '6 Bulan Terakhir',
            '1_tahun' => '1 Tahun Terakhir',
        ];
    }

    protected function getData(): array
    {
        $startDate = match ($this->filter) {
            '3_bulan' => now()->subMonths(3),
            '1_tahun' => now()->subYear(),
            default => now()->subMonths(6),
        };

        $data = Trend::model(Permohonan::class)
            ->between(
                start: $startDate,
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Permohonan Masuk',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#4e73df',
                    'fill' => false,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('M')),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Permohonan'
                    ]
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
