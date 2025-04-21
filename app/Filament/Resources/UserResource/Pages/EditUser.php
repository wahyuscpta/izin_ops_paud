<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $breadcrumb = 'Edit';

    protected static ?string $title = 'Data Pengguna';

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            if (empty($data['password'])) {
                unset($data['password']);
            }
            
            $record->update($data);
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
            ->duration(8000)
            ->persistent()
            ->actions([
                Action::make('reload')
                    ->label('Muat Ulang')
                    ->action(fn () => $this->redirect(request()->header('Referer')))
            ])
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