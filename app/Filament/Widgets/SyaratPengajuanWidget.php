<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SyaratPengajuanWidget extends Widget
{
    protected static string $view = 'filament.widgets.syarat-pengajuan-widget';   

    public static function canView(): bool
    {
        return Auth::user()->hasRole(['pemohon']);
    }
}
