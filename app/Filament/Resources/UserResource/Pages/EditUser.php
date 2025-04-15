<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $breadcrumb = 'Edit';

    protected static ?string $title = 'Data Pengguna';

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
