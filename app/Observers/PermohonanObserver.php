<?php

namespace App\Observers;

use App\Models\Permohonan;
use App\Models\User;
use App\Notifications\EmailStatusNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class PermohonanObserver
{
    /**
     * Handle the Permohonan "created" event.
     */
    public function created(Permohonan $permohonan): void
    {

        $status = $permohonan->status_permohonan;
        $pemohon = $permohonan->user;
        $admins = User::role('admin')->get();

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

                // Notifikasi Email untuk Admin
                $admin->notify(new EmailStatusNotification(
                    $permohonan, 
                    'new_submission', 
                    'admin', 
                    // route('admin.permohonan.verify', $permohonan->id)
                ));

                $pemohon->notify(new EmailStatusNotification(
                    $permohonan, 
                    'new_submission', 
                    'pemohon', 
                    // route('permohonan.detail', $permohonan->id)
                ));
            }
        }
    }

    /**
     * Handle the Permohonan "updated" event.
     */
    public function updated(Permohonan $permohonan): void
    {
        if ($permohonan->wasChanged('status_permohonan')) {
            $status = $permohonan->status_permohonan;
            $pemohon = $permohonan->user;
            $admins = User::role('admin')->get();
            $kepalaDinas = User::role('kepala_dinas')->get();
            $permohonan->previous_status = $permohonan->getOriginal('status_permohonan');

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
    
                    // Notifikasi Email untuk Admin
                    $admin->notify(new EmailStatusNotification(
                        $permohonan, 
                        'new_submission', 
                        'admin', 
                        // route('admin.permohonan.verify', $permohonan->id)
                    ));
    
                    $pemohon->notify(new EmailStatusNotification(
                        $permohonan, 
                        'new_submission', 
                        'pemohon', 
                        // route('permohonan.detail', $permohonan->id)
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
                    // route('permohonan.detail', $permohonan->id)
                ));
            }

            if ($status === 'menunggu_validasi_lapangan') {

                Notification::make()
                    ->title('Permohonan Telah Diverifikasi')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('primary')
                    ->body('Permohonan Anda telah diverifikasi dan masuk tahap validasi lapangan.')
                    ->sendToDatabase($pemohon);

                $pemohon->notify(new EmailStatusNotification(
                    $permohonan, 
                    'status_update', 
                    'pemohon', 
                    // route('permohonan.detail', $permohonan->id)
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
                    // route('permohonan.detail', $permohonan->id)
                ));

                Notification::make()
                    ->title('Permohonan Siap Diterbitkan')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('primary')
                    ->body('Permohonan dari ' . $pemohon->name . ' menunggu proses penerbitan izin.')
                    ->sendToDatabase($kepalaDinas);

                foreach ($kepalaDinas as $kepala) {
                    $kepala->notify(new EmailStatusNotification(
                        $permohonan,
                        'status_update',
                        'kepala_dinas',
                        // route('permohonan.detail', $permohonan->id)
                    ));
                }

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
                    // route('permohonan.detail', $permohonan->id)
                ));

                foreach ($admins as $admin) {
                    Notification::make()
                    ->title('Izin Telah Diterbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->body('Permohonan atas nama ' . $pemohon->name . ' telah disetujui dan izin sudah diterbitkan.')
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($admin);
                }   
            }
        }
    }

    /**
     * Handle the Permohonan "deleted" event.
     */
    public function deleted(Permohonan $permohonan): void
    {
        //
    }

    /**
     * Handle the Permohonan "restored" event.
     */
    public function restored(Permohonan $permohonan): void
    {
        //
    }

    /**
     * Handle the Permohonan "force deleted" event.
     */
    public function forceDeleted(Permohonan $permohonan): void
    {
        //
    }
}
