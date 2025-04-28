<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PermohonanController extends Controller
{
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