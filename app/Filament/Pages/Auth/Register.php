<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

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
                ->rule('min:0')
                ->rule('regex:/^08[0-9]{8,11}$/')
                ->maxLength(13)
                ->validationMessages([
                    'required' => 'Nomor telepon wajib diisi',
                    'tel' => 'Nomor telepon hanya boleh berisi angka',
                    'regex' => 'Nomor telepon harus diawali 08 dan memiliki 10-13 digit',
                    'max' => 'Nomor telepon maksimal 13 digit',
                ])
                ->extraInputAttributes([
                    'min' => '0',
                    'pattern' => '[0-9]*',
                    'inputmode' => 'numeric'
                ]);
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'Nama wajib diisi',
                    'max' => 'Nama maksimal 255 karakter',
                ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique('users', 'email')
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'Email wajib diisi',
                    'email' => 'Format email tidak valid',
                    'unique' => 'Email sudah terdaftar',
                    'max' => 'Email maksimal 255 karakter',
                ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
                ->label('Kata Sandi')
                ->password()
                ->required()
                ->minLength(8)
                ->rules([
                    'regex:/[A-Z]/',
                    'regex:/[a-z]/',
                    'regex:/[0-9]/',
                ])
                ->validationMessages([
                    'required' => 'Kata sandi wajib diisi',
                    'min' => 'Kata sandi minimal 8 karakter',
                    'regex' => 'Kata sandi harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus',
                ])
                ->revealable()
                ->autocomplete('new-password')
                ->dehydrateStateUsing(fn ($state) => Hash::make($state));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
                ->label('Konfirmasi Kata Sandi')
                ->password()
                ->required()
                ->same('password')
                ->validationMessages([
                    'required' => 'Konfirmasi kata sandi wajib diisi',
                    'same' => 'Konfirmasi kata sandi tidak cocok',
                ])
                ->autocomplete('new-password')
                ->revealable();
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Sudah punya akun? Masuk')
            ->url(filament()->getLoginUrl());
    }

    public function getHeading(): string | Htmlable
    {
        return __('Daftar Akun');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Buat Akun')
            ->submit('authenticate');
    }

}
