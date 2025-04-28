<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PermohonanController extends Controller
{
    public function generateSertifikat(Request $request, $id)
    {
        $permohonan = Permohonan::with(['identitas', 'penyelenggara', 'user'])->findOrFail($id);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

        $bgPath = public_path('images/bg-sertifikat.png');
        $background = 'data:image/png;base64,' . base64_encode(file_get_contents($bgPath));
        
        $data = [
            'permohonan' => $permohonan,
            'identitas' => $permohonan->identitas,
            'penyelenggara' => $permohonan->penyelenggara,
            'user' => $permohonan->user,
            'logo' => $logoBase64,
            'background' => $background
        ];
        
        $pdf = PDF::loadView('filament.permohonan.pdf-sertifikat', $data);
        
        $pdf->setPaper('legal', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'times',
            'isPhpEnabled' => true,
            'isJavascriptEnabled' => false,
        ]);
        
        $filename = 'SK_IZIN_' . $permohonan->nomor_sk . '_' . $permohonan->id . '.pdf';
    
        return $request->has('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    public function downloadAllDocuments(Permohonan $permohonan)
    {
        $lampiran = $permohonan->lampiran;

        if ($lampiran->isEmpty()) {
            session()->flash('error', 'Tidak ada dokumen yang tersedia untuk diunduh.');
            return redirect()->back();
        }

        $zipFileName = "Dokumen_Permohonan_{$permohonan->identitas->nama_lembaga}.zip";
        $tempPath = storage_path('app/temp');
        $zipFilePath = "{$tempPath}/{$zipFileName}";

        // Pastikan direktori temp ada
        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        // Hapus file zip yang mungkin sudah ada
        if (file_exists($zipFilePath)) {
            unlink($zipFilePath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            session()->flash('error', 'Gagal membuat file zip.');
            return redirect()->back();
        }

        foreach ($lampiran as $dokumen) {
            $filePath = storage_path("app/{$dokumen->file_path}");
            
            if (file_exists($filePath)) {
                // Gunakan nama original file untuk nama di dalam zip
                $fileExtension = pathinfo($dokumen->file_path, PATHINFO_EXTENSION);
                $namaFile = "{$dokumen->nama}.{$fileExtension}";
                
                $zip->addFile($filePath, $namaFile);
            }
        }

        $zip->close();

        if (!file_exists($zipFilePath)) {
            session()->flash('error', 'Gagal membuat file zip.');
            return redirect()->back();
        }

        return response()->download($zipFilePath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}