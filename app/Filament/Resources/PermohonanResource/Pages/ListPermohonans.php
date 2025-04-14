<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPermohonans extends ListRecords
{
    protected static string $resource = PermohonanResource::class;

    protected static ?string $title = 'Permohonan';

    protected static ?string $breadcrumb = 'Daftar Permohonan';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Permohonan Baru')
            ->icon('heroicon-o-plus'),
        ];
    }
}
