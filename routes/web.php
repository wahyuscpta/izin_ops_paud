<?php

use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\PermohonanExportController;
use App\Http\Controllers\SKIzinController;
use App\Models\Lampiran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Generate Dokumen to PDF
Route::get('/permohonan/{id}/export-pdf', [PermohonanExportController::class, 'export'])
    ->name('permohonan.export.pdf');
Route::get('/permohonan/{id}/sk-izin-operasional', [PermohonanController::class, 'generateSK'])
    ->name('sk-izin.generate-pdf');
Route::get('/permohonan/{id}/sertifikat-izin-operasional', [PermohonanController::class, 'generateSertifikat'])
    ->name('sertifikat.pdf');

// Download Dokumen
Route::get('/permohonan/{id}/download-sk-izin', [PermohonanController::class, 'downloadSKIzin'])
    ->name('download.sk-izin');
Route::get('/permohonan/{id}/download-sertifikat', [PermohonanController::class, 'downloadSertifikat'])
    ->name('download.sertifikat');

// Generate All Dokumen to ZIP
Route::get('/permohonan/{permohonan}/download-all', [PermohonanController::class, 'downloadAllDokumen'])
    ->name('permohonan.download-all')
    ->middleware(['auth']);

// Viewed Docs
Route::get('/view-document/{lampiran}', function (Lampiran $lampiran) {
    // Update viewed_docs
    $lampiran->update([
        'viewed' => true,
        'viewedBy' => Auth::id(),
    ]);
    // Redirect ke file
    return redirect()->away(asset('storage/' . $lampiran->lampiran_path));
})->name('view-document');
    
Route::get('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('filament.admin.auth.login');
})->name('logout');