<?php

namespace App\Observers;

use App\Models\Permohonan;
use App\Models\User;
use App\Notifications\EmailStatusNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class PermohonanObserver
{
    public function created(Permohonan $permohonan): void
    {

        // Ambil status awal dari permohonan yang baru dibuat
        $status = $permohonan->status_permohonan;
        // Ambil user yang mengajukan permohonan (relasi 'user' dari model Permohonan)
        $pemohon = $permohonan->user;
        // Ambil seluruh user yang memiliki peran 'admin' untuk dikirimi notifikasi
        $admins = User::role('admin')->get();

        if ($status === 'menunggu_verifikasi') {
            // Kirim notifikasi berbasis database ke admin
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Permohonan Baru Masuk')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('primary')
                    ->body('Permohonan dari ' . $pemohon->name . ' menunggu verifikasi.')
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(fn () => route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id]))
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($admin);

                // Kirim notifikasi email ke admin terkait adanya permohonan baru
                $admin->notify(new EmailStatusNotification(
                    $permohonan, 
                    'new_submission', 
                    'admin', 
                    route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
                ));

                // Kirim notifikasi email ke pemohon sebagai konfirmasi bahwa permohonan berhasil diajukan
                $pemohon->notify(new EmailStatusNotification(
                    $permohonan, 
                    'new_submission', 
                    'pemohon', 
                    route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
                ));
            }
        }
    }

    public function updated(Permohonan $permohonan): void
    {
        if ($permohonan->isDirty('tanggal_kunjungan') && $permohonan->getOriginal('tanggal_kunjungan') !== null) {
            
            $tanggal = $tanggal = \Carbon\Carbon::parse($permohonan->tanggal_kunjungan)->format('d F Y');
            $pemohon = $permohonan->user;

            Notification::make()
            ->title('Perubahan Tanggal Kunjungan')
            ->icon('heroicon-o-information-circle')
            ->iconColor('primary')
            ->body('Tanggal kunjungan lapangan untuk permohonan anda telah diubah ke tanggal ' . $tanggal  .' . Mohon segera lakukan pengecekan dan verifikasi.')
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(fn () => route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($pemohon);

            $pemohon->notify(new EmailStatusNotification(
                $permohonan, 
                'tanggal_update', 
                'pemohon', 
                route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
            ));
        }

        // Periksa apakah status_permohonan mengalami perubahan setelah update
        if ($permohonan->wasChanged('status_permohonan')) {

            // Ambil status baru dari permohonan
            $status = $permohonan->status_permohonan;
            // Ambil user yang mengajukan permohonan
            $pemohon = $permohonan->user;
            // Ambil semua pengguna dengan peran admin
            $admins = User::role('admin')->get();
            // Ambil tanggal kunjungan dari permohonan
            $tanggal = \Carbon\Carbon::parse($permohonan->tanggal_kunjungan)->format('d F Y');

            if ($status === 'menunggu_verifikasi') {
                foreach ($admins as $admin) {
                    Notification::make()
                        ->title('Permohonan Baru Masuk')
                        ->icon('heroicon-o-information-circle')
                        ->iconColor('primary')
                        ->body('Permohonan dari ' . $pemohon->name . ' menunggu verifikasi.')
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url(fn () => route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id]))
                                ->markAsRead(),
                        ])
                        ->sendToDatabase($admin);
    
                    $admin->notify(new EmailStatusNotification(
                        $permohonan, 
                        'new_submission', 
                        'admin', 
                        route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
                    ));
    
                    $pemohon->notify(new EmailStatusNotification(
                        $permohonan, 
                        'new_submission', 
                        'pemohon', 
                        route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
                    ));
                }
            }

            if ($status === 'permohonan_ditolak') {
                Notification::make()
                    ->title('Permohonan Ditolak')
                    ->icon('heroicon-o-x-circle')
                    ->iconColor('danger')
                    ->body('Permohonan Anda Ditolak karena ' . $permohonan->catatan)
                    ->sendToDatabase($pemohon);

                $pemohon->notify(new EmailStatusNotification(
                    $permohonan, 
                    'status_update', 
                    'pemohon', 
                    route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
                ));
            }

            if ($status === 'menunggu_validasi_lapangan') {

                Notification::make()
                    ->title('Permohonan Telah Diverifikasi')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('primary')
                    ->body('Permohonan Anda telah diverifikasi dan masuk tahap validasi lapangan. Kunjungan dijadwalkan pada tanggal ' . $tanggal . '.')
                    ->sendToDatabase($pemohon);

                $pemohon->notify(new EmailStatusNotification(
                    $permohonan, 
                    'status_update', 
                    'pemohon', 
                    route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
                ));
            }

            if ($status === 'proses_penerbitan_izin') {
                Notification::make()
                    ->title('Validasi Lapangan Selesai')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('primary')
                    ->body('Permohonan Anda sedang diproses untuk penerbitan izin.')
                    ->sendToDatabase($pemohon);

                $pemohon->notify(new EmailStatusNotification(
                    $permohonan, 
                    'status_update', 
                    'pemohon', 
                    route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
                ));

            }

            if ($status === 'izin_diterbitkan') {
                Notification::make()
                    ->title('Izin Telah Diterbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->body('Permohonan Anda telah disetujui dan izin sudah diterbitkan.')
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(fn () => route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id]))
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($pemohon);

                $pemohon->notify(new EmailStatusNotification(
                    $permohonan, 
                    'status_update', 
                    'pemohon', 
                    route('filament.admin.resources.permohonans.view', ['record' => $permohonan->id])
                ));  
            }
        }
    }

    public function deleted(Permohonan $permohonan): void
    {
        //
    }

    public function restored(Permohonan $permohonan): void
    {
        //
    }

    public function forceDeleted(Permohonan $permohonan): void
    {
        //
    }
}
