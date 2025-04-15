<?php

use App\Http\Controllers\PermohonanExportController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/permohonan/{id}/export-pdf', [PermohonanExportController::class, 'export'])->name('permohonan.export.pdf');