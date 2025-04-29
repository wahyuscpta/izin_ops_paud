<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail
{
    // Properti untuk menyimpan URL verifikasi
    public $url;

    public function toMail($notifiable)
    {
        $verificationUrl = $this->url ?? $this->verificationUrl($notifiable);
        
        // Pastikan komponen variabel yang diperlukan tersedia
        return (new MailMessage)
            ->subject('Verifikasi Email Anda')
            ->view('emails.verify', [
                'url' => $verificationUrl, 
                'user' => $notifiable,
                'app_name' => config('app.name')
            ]);
    }
}