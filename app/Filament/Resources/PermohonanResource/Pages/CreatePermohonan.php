<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePermohonan extends CreateRecord
{
    protected static string $resource = PermohonanResource::class;

    protected static ?string $breadcrumb = 'Pengajuan Baru';

    protected static ?string $title = 'Formulir Permohonan';

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
                // $this->isKirimPermohonan = false;
                $this->create();
            });
    }
}
