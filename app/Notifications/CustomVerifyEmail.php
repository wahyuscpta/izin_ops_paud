<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail
{
    // Properti opsional untuk menampung URL verifikasi yang dikustom
    public $url;

    // Override method bawaan untuk mengirim email menggunakan custom view
    public function toMail($notifiable)
    {
         // Gunakan URL yang dikirim dari luar, atau fallback ke bawaan Laravel
        $verificationUrl = $this->url ?? $this->verificationUrl($notifiable);
        
        return (new MailMessage)
            ->subject('Verifikasi Email Anda')          // Subjek email
            ->view('emails.verify', [                   // Custom Blade view
                'url' => $verificationUrl,              // Kirim URL verifikasi ke view
                'user' => $notifiable,                  // Data user penerima
                'app_name' => config('app.name')        // Nama aplikasi dari konfigurasi
            ]);
    }
}