<?php

namespace App\Providers;

use App\Notifications\CustomVerifyEmail;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Auth;
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

        Filament::serving(function () {
            Filament::registerNavigationItems([
                NavigationItem::make('Logout')
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->group('Pengaturan Akun')
                    ->url(route('logout'))
                    ->sort(2)
            ]);
        });
    }
}
