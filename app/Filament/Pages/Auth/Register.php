<?php

namespace App\Filament\Pages\Auth;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getTeleponFormComponent(), 
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getTeleponFormComponent(): Component
    {
        return TextInput::make('no_telepon')
                ->label('No Telepon')
                ->numeric()
                ->required()
                ->rule('regex:/^08[0-9]{8,11}$/')
                ->maxLength(13);
    }
}
