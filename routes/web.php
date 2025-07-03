<?php

use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\PermohonanExportController;
use App\Http\Controllers\SKIzinController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/permohonan/{id}/export-pdf', [PermohonanExportController::class, 'export'])
    ->name('permohonan.export.pdf');
Route::get('/permohonan/{id}/sk-izin-izin-operasional', [SKIzinController::class, 'generatePDF'])
    ->name('sk-izin.generate-pdf');
Route::get('/permohonan/{id}/sertifikat-izin-operasional', [PermohonanController::class, 'generateSertifikat'])
    ->name('sertifikat.pdf');

Route::get('/permohonan/{id}/download-sk-izin', [PermohonanController::class, 'downloadSKIzin'])
    ->name('download.sk-izin');
Route::get('/permohonan/{id}/download-sertifikat', [PermohonanController::class, 'downloadSertifikat'])
    ->name('download.sertifikat');

Route::get('/permohonan/{permohonan}/download-all', [PermohonanController::class, 'downloadAllDokumen'])
    ->name('permohonan.download-all')
    ->middleware(['auth']);
    
Route::get('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('filament.admin.auth.login');
})->name('logout');