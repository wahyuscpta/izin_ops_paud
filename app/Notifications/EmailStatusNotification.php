<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $permohonan;
    protected $notificationType;
    protected $userRole;
    protected $actionUrl;
    protected $formattedStatus;

    public function __construct($permohonan, $notificationType, $userRole, $actionUrl = null)
    {
        $this->permohonan = $permohonan;
        $this->notificationType = $notificationType;
        $this->userRole = $userRole;
        $this->actionUrl = $actionUrl ?? '#'; // Provide a default value
                
        // Mengubah format status ke format yang lebih mudah dibaca
        $this->formattedStatus = $this->formatStatus($permohonan->status_permohonan);    
        
        // Memasukkan catatan sebagai notes jika ada
        if (isset($permohonan->catatan)) {
            $this->permohonan->notes = $permohonan->catatan;
        }
        
        // Ensure identitas is loaded
        if ($permohonan->relationLoaded('identitas') === false && method_exists($permohonan, 'identitas')) {
            $permohonan->load('identitas');
        }
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->getEmailSubject();
            
        $mail = (new MailMessage)
            ->subject($subject);

        $formattedStatus = $this->formatStatus($this->permohonan->status_permohonan);
            
        return $mail->view('emails.status', [
            'user' => $notifiable,
            'permohonan' => $this->permohonan,
            'notificationType' => $this->notificationType,
            'userRole' => $this->userRole,
            'actionUrl' => $this->actionUrl,
            'formattedStatus' => $formattedStatus
        ]);
    }

    protected function getEmailSubject()
    {
        if ($this->notificationType == 'new_submission') {
            if ($this->userRole == 'pemohon') {
                return 'Permohonan Izin Operasional PAUD Berhasil Diajukan';
            } else {
                return 'Permohonan Izin Operasional PAUD Baru Memerlukan Verifikasi';
            }
        } elseif ($this->notificationType == 'status_update') {
            if ($this->userRole == 'pemohon') {
                return 'Status Permohonan Izin Operasional PAUD: ' . $this->formattedStatus;
            } else {
                return 'Permohonan Izin Operasional PAUD Memerlukan Persetujuan';
            }
        } elseif ($this->notificationType == 'reminder') {
            return 'Pengingat: Permohonan Izin Operasional PAUD Menunggu Tindakan';
        }
        
        return 'Notifikasi Sistem Permohonan Izin Operasional PAUD';
    }
    
    protected function formatStatus($status)
    {
        $statuses = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'permohonan_ditolak' => 'Ditolak',
            'menunggu_validasi_lapangan' => 'Menunggu Validasi Lapangan',
            'proses_penerbitan_izin' => 'Proses Penerbitan Izin',
            'izin_diterbitkan' => 'Izin Diterbitkan'
        ];
        
        return $statuses[$status] ?? $status;
    }
}