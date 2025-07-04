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

    // Konstruktor notifikasi yang menerima data dari observer
    public function __construct($permohonan, $notificationType, $userRole, $actionUrl = null)
    {
        // Simpan data permohonan yang dikirim
        $this->permohonan = $permohonan;
        // Simpan jenis notifikasi berdasarkan status permohonan
        $this->notificationType = $notificationType;
        // Simpan peran pengguna yang menerima notifikasi
        $this->userRole = $userRole;
        // Gunakan action URL yang dikirim, atau default ke '#' jika tidak ada
        $this->actionUrl = $actionUrl ?? '#';
        // Format status permohonan agar tampil lebih rapi
        $this->formattedStatus = $this->formatStatus($permohonan->status_permohonan);    
        // Jika ada catatan pada permohonan, simpan ke properti notes 
        if (isset($permohonan->catatan)) {
            $this->permohonan->notes = $permohonan->catatan;
        }
        // Jika relasi 'identitas' belum dimuat, muat relasinya agar bisa diakses di notifikasi
        if ($permohonan->relationLoaded('identitas') === false && method_exists($permohonan, 'identitas')) {
            $permohonan->load('identitas');
        }
    }

    // Menentukan jalur notifikasi, ini dikirim melalui email
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Ambil subjek email yang disesuaikan berdasarkan jenis notifikasi
        $subject = $this->getEmailSubject();            
        // Buat instance MailMessage dengan subjek yang sudah ditentukan
        $mail = (new MailMessage)->subject($subject);
        // Format status permohonan agar tampil lebih rapi dan mudah dibaca
        $formattedStatus = $this->formatStatus($this->permohonan->status_permohonan);
            
        // Kembalikan email yang menggunakan custom view
        return $mail->view('emails.status', [                    // Custom blade view
            'user' => $notifiable,                               // User yang menerima notifikasi
            'permohonan' => $this->permohonan,                   // Objek permohonan yang berkaitan
            'notificationType' => $this->notificationType,       // Jenis notifikasi
            'userRole' => $this->userRole,                       // Peran user penerima
            'actionUrl' => $this->actionUrl,                     // Link aksi (link ke detail permohonan)
            'formattedStatus' => $formattedStatus                // Status yang sudah diformat agar lebih informatif
        ]);
    }

    // Membuat subjek email berdasarkan jenis notifikasi dan role penerima
    protected function getEmailSubject()
    {
        // Jika jenis notifikasi adalah 'new_submission'
        if ($this->notificationType == 'new_submission') {
            // Jika yang menerima adalah pemohon, kirim subjek konfirmasi pengajuan
            if ($this->userRole == 'pemohon') {
                return 'Permohonan Izin Operasional PAUD Berhasil Diajukan';
            } else {
                // Jika admin, subjek berupa permintaan verifikasi
                return 'Permohonan Izin Operasional PAUD Baru Memerlukan Verifikasi';
            }

        // Jika jenis notifikasi adalah 'status_update'
        } elseif ($this->notificationType == 'status_update') {
            // Jika yang menerima adalah pemohon, subjek mencantumkan status terbaru
            if ($this->userRole == 'pemohon') {
                return 'Status Permohonan Izin Operasional PAUD: ' . $this->formattedStatus;
            } else {
                // Jika admin, berarti memerlukan persetujuan lanjutan
                return 'Permohonan Izin Operasional PAUD Memerlukan Persetujuan';
            }

        // Jika jenis notifikasi adalah 'reminder'
        } elseif ($this->notificationType == 'tanggal_update') {
            // Subjek berupa pengingat tindakan terhadap permohonan
            return 'Perubahan Tanggal Kunjungan Lapangan';
        
        // Jika jenis notifikasi adalah 'reminder'
        } elseif ($this->notificationType == 'reminder') {
            // Subjek berupa pengingat tindakan terhadap permohonan
            return 'Pengingat: Permohonan Izin Operasional PAUD Menunggu Tindakan';
        }

        // Default subjek jika jenis notifikasi tidak dikenali
        return 'Notifikasi Sistem Permohonan Izin Operasional PAUD';
    }
    
    // Mengubah nilai status permohonan menjadi format teks yang lebih terbaca
    protected function formatStatus($status)
    {
        // Daftar status
        $statuses = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'permohonan_ditolak' => 'Ditolak',
            'menunggu_validasi_lapangan' => 'Menunggu Validasi Lapangan',
            'proses_penerbitan_izin' => 'Proses Penerbitan Izin',
            'izin_diterbitkan' => 'Izin Diterbitkan'
        ];
        
        // Kembalikan teks yang sudah diformat jika tersedia, jika tidak, kembalikan string asli
        return $statuses[$status] ?? $status;
    }
}