<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Config;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

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

    // Memodifikasi data input sebelum proses penyimpanan dilakukan
    protected function mutateFormDataBeforeCreate(array $data): array
    {       
         // Jika field 'roles' diatur dan bernilai 2 (diasumsikan sebagai role admin)
        if (isset($data['roles']) && $data['roles'] == 2) {          
            // Ambil PIN admin terenkripsi dari tabel konfigurasi  
            $storedEncryptedPin = Config::where('key', 'admin_creation_pin')->value('value');
    
            try {
                // Dekripsi PIN untuk dibandingkan dengan input pengguna
                $storedPin = Crypt::decryptString($storedEncryptedPin);
            } catch (\Exception $e) {
                // Jika gagal dekripsi, tampilkan notifikasi error dan hentikan proses
                $this->showErrorNotification('PIN sistem tidak dapat dibaca.');
                throw new Halt();
            }
    
            // Validasi PIN yang diinput dengan PIN yang disimpan di sistem
            if (!isset($data['admin_pin']) || $data['admin_pin'] !== $storedPin) {
                // Jika PIN tidak sesuai atau kosong, tampilkan notifikasi error dan hentikan proses
                $this->showErrorNotification('PIN yang dimasukkan salah.');
                throw new Halt();
            }

            // Hapus field 'admin_pin' dari data agar tidak disimpan ke database
            unset($data['admin_pin']);
        }

        // Jika user yang sedang login memiliki peran admin atau super admin
        if (Auth::user()?->hasAnyRole(['admin', 'super_admin'])) {
             // Tandai email sebagai sudah terverifikasi secara otomatis
            $data['email_verified_at'] = now();
        }

        return $data;
    }  

    // Menangani proses pembuatan record baru di database
    protected function handleRecordCreation(array $data): Model
    {
        try {
            $record =  static::getModel()::create($data);

            // Log aktivitas
            activity()
                ->causedBy(Auth::user())
                ->performedOn($record)
                ->withProperties([
                    'attributes' => [
                        'name' => $record->name,
                        'role' => $record->getRoleNames()->first(),
                    ]
                ])
                ->event('created')
                ->useLog('Pengguna')
                ->log('Telah membuat akun pengguna baru atas nama ' . $record->name . ' dengan peran: ' . Str::upper($record->getRoleNames()->first()) . '.');
            
            return $record;

        } catch (\Exception $e) {
            $this->showErrorNotification('Gagal menyimpan data: ' . $e->getMessage());
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
            ->duration(5000)
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