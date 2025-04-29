<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Pengguna';

    protected static ?string $breadcrumb = 'Daftar Pengguna';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Pengguna Baru')
            ->icon('heroicon-o-plus'),
        ];
    }
}
