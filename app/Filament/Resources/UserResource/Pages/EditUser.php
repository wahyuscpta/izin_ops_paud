<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Config;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $breadcrumb = 'Edit';

    protected static ?string $title = 'Data Pengguna';

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['roles']) && $data['roles'] == 2) {          
            $storedEncryptedPin = Config::where('key', 'admin_creation_pin')->value('value');
    
            try {
                $storedPin = Crypt::decryptString($storedEncryptedPin);
            } catch (\Exception $e) {
                $this->showErrorNotification('PIN sistem tidak dapat dibaca.');
                throw new Halt();
            }
    
            if (!isset($data['admin_pin']) || $data['admin_pin'] !== $storedPin) {
                $this->showErrorNotification('PIN yang dimasukkan salah.');
                throw new Halt();
            }

            unset($data['admin_pin']);
        }

        return $data;
    } 

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            if (empty($data['password'])) {
                unset($data['password']);
            }
            
            $record->update($data);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($record)
                ->withProperties([
                    'attributes' => [
                        'name' => $record->name,
                    ]
                ])
                ->event('updated')
                ->useLog('Pengguna')
                ->log('Telah mengubah akun pengguna dengan nama ' . $record->name);

            return $record;
        } catch (\Exception $e) {
            $this->showErrorNotification('Gagal menyimpan data: ' . $e->getmessage());
            throw new Halt();
        }
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil Diperbarui!')
            ->body('Data pengguna berhasil diperbarui')
            ->duration(5000);
    }

    protected function showErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Gagal Diperbarui!')
            ->body($message)
            ->danger()
            ->duration(5000)
            ->persistent()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save')
                ->keyBindings(['mod+s']),
            
            Action::make('cancel')
                ->label('Batal')
                ->url($this->getRedirectUrl())
                ->color('gray'),
        ];
    }
}