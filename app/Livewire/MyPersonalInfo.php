<?php

namespace App\Livewire;

use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;

class MyPersonalInfo extends PersonalInfo
{
    public array $only = [
        'name', 'email', 'no_telepon',
    ];

    protected function getNoTeleponComponent(): TextInput
    {
        return TextInput::make('no_telepon')
            ->numeric()
            ->required()
            ->rule('regex:/^08[0-9]{8,11}$/')
            ->maxLength(13)
            ->label(__('filament-breezy::default.fields.no_telepon'));            
    }

    protected function getProfileFormSchema(): array
    {
        $groupFields = Group::make([
            $this->getNameComponent(),
            $this->getEmailComponent(),
            $this->getNoTeleponComponent(),
        ])->columnSpan(2);

        return ($this->hasAvatars)
            ? [filament('filament-breezy')->getAvatarUploadComponent(), $groupFields]
            : [$groupFields];
    }
}