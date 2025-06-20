<?php

namespace App\Filament\Pages;

use App\Models\Config;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class GenerateAdminPin extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static string $view = 'filament.pages.generate-admin-pin';
    protected static ?string $navigationLabel = 'Generate PIN';
    protected static ?string $title = 'Generate PIN Admin';
    protected static ?string $navigationGroup = 'Manajemen Sistem';

    public ?array $data = [];

    public function mount(): void
    {
        $currentPin = $this->getCurrentPin();
        
        $this->form->fill([
            'pin' => $currentPin ?? ''
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('PIN Admin')
                    ->description('Kelola PIN untuk pembuatan akun admin baru')
                    ->schema([
                        TextInput::make('pin')
                            ->label('PIN Admin')
                            ->required()
                            ->minLength(6)
                            ->maxLength(10)
                            ->password() 
                            ->revealable()                       
                            ->autofocus()
                            ->helperText('PIN harus terdiri dari 6-10 digit angka'),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        try {
            $data = $this->form->getState();
            
            Config::updateOrCreate(
                ['key' => 'admin_creation_pin'],
                ['value' => Crypt::encryptString($data['pin'])]
            );

            Notification::make()
                ->title('PIN berhasil diperbarui dan dienkripsi.')
                ->body("PIN yang disimpan: {$data['pin']}")
                ->success()
                ->send();

            $this->mount();

        } catch (Halt $exception) {
            return;
        }
    }

    public function generatePin(): void
    {
        $newPin = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->form->fill([
            'pin' => $newPin
        ]);

        Notification::make()
            ->title('PIN baru telah digenerate')
            ->body("PIN: {$newPin} - Klik 'Simpan PIN' untuk menyimpannya")
            ->info()
            ->persistent()
            ->send();
    }

    private function getCurrentPin(): ?string
    {
        try {
            $encryptedPin = Config::where('key', 'admin_creation_pin')->value('value');
            
            if ($encryptedPin) {
                return Crypt::decryptString($encryptedPin);
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan PIN')
                ->action('submit')
                ->color('primary'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate PIN')
                ->action('generatePin')
                ->icon('heroicon-o-sparkles')
                ->color('gray'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole(['super_admin']);   
    }
}