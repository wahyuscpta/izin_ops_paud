<?php

namespace App\Filament\Resources\PermohonanResource\Pages;

use App\Filament\Resources\PermohonanResource;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPermohonan extends ViewRecord implements HasForms
{
    protected static string $resource = PermohonanResource::class;

    protected static bool $canCreateAnother = false;

    public static string $view = 'filament.permohonan.view-permohonan';

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
