<?php

namespace App\Providers;

use App\Models\Permohonan;
use App\Notifications\CustomVerifyEmail;
use App\Observers\PermohonanObserver;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Support\Facades\FilamentView;
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

        FilamentView::registerRenderHook(
            'panels::head.end',
            fn () => view('filament.components.sidebar')
        );

        Permohonan::observe(PermohonanObserver::class);
    }
}
