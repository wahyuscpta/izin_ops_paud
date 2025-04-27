<?php

use App\Http\Controllers\PermohonanExportController;
use App\Http\Controllers\SKIzinController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/permohonan/{id}/export-pdf', [PermohonanExportController::class, 'export'])->name('permohonan.export.pdf');
Route::get('/sk-izin/generate-pdf/{id}', [SKIzinController::class, 'generatePDF'])->name('sk-izin.generate-pdf');

Route::get('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('filament.admin.auth.login');
})->name('logout');