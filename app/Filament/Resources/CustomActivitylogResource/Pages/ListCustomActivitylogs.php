<?php

namespace App\Filament\Resources\CustomActivitylogResource\Pages;

use App\Filament\Resources\CustomActivitylogResource;
use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class ListCustomActivitylogs extends ListRecords
{
    protected static string $resource = CustomActivitylogResource::class;

    protected static ?string $breadcrumb = 'Daftar Aktivitas';

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('log_name')
                ->label('Jenis Log')
                ->options([
                    'Permohonan' => 'Permohonan',
                    'Default' => 'Lainnya',
                ]),

            SelectFilter::make('event')
                ->label('Aksi')
                ->options([
                    'created' => 'Dibuat',
                    'updated' => 'Diperbarui',
                    'deleted' => 'Dihapus',
                ]),

            SelectFilter::make('causer_id')
                ->label('Pengguna')
                ->relationship('causer', 'name'),

            Filter::make('waktu')
                ->label('Rentang Waktu')
                ->form([
                    DatePicker::make('from')->label('Dari'),
                    DatePicker::make('until')->label('Sampai'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                }),
        ];
    }
}
