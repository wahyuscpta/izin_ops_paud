<?php

namespace App\Providers;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {   
        // Mengganti notifikasi verifikasi email default dengan yang kustom
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $customEmail = new CustomVerifyEmail();
            $customEmail->url = $url;
            return $customEmail->toMail($notifiable);
        });    
    }
}
