<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SKIzinController extends Controller
{
    public function generatePDF(Request $request, $id)
    {
        // Ambil data permohonan beserta relasi
        $permohonan = Permohonan::with(['identitas', 'penyelenggara', 'user'])->findOrFail($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        
        // Pastikan data diformat sebagai array untuk view
        $data = [
            'permohonan' => $permohonan,
            'identitas' => $permohonan->identitas,
            'penyelenggara' => $permohonan->penyelenggara,
            'user' => $permohonan->user,
            'logo' => $logoBase64
            // Data lain yang mungkin diperlukan
        ];
        
        // Load view dengan data yang benar
        $pdf = PDF::loadView('filament.permohonan.pdf-izin-operasional', $data);
        
        // Konfigurasi PDF
        $pdf->setPaper('legal');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'times',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false,
        ]);
        
        // Tentukan nama file dengan format yang lebih baik
        $filename = 'SK_IZIN_' . $permohonan->nomor_sk . '_' . $permohonan->id . '.pdf';
        
        // Return berdasarkan parameter request
        return $request->has('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
