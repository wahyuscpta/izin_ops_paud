<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $breadcrumb = 'Tambah';

    protected static ?string $title = 'Pengguna Baru';

    protected static bool $canCreateAnother = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {       
        if (Auth::user()?->hasAnyRole(['admin', 'super_admin'])) {
            $data['email_verified_at'] = now();
        }

        return $data;
    }  

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return static::getModel()::create($data);
        } catch (\Exception $e) {
            $this->showErrorNotification('Gagal menyimpan data: ' . $e->getmessage());
            throw new Halt();
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil Disimpan!')
            ->body('Data pengguna baru berhasil disimpan')
            ->duration(5000);
    }

    protected function showErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Gagal Disimpan!')
            ->body($message)
            ->danger()
            ->duration(8000)
            ->persistent()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Simpan Data')
                ->submit('create')
                ->keyBindings(['mod+s']),
            
            Action::make('cancel')
                ->label('Batal')
                ->url($this->getRedirectUrl())
                ->color('gray'),
        ];
    }
}