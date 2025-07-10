<?php

namespace App\Http\Controllers;

use App\Models\Lampiran;
use App\Models\Permohonan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;

class PermohonanController extends Controller
{
    // Generate PDF Draft SK
    public function generateSK(Request $request, $id)
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
        $filename = 'SK_IZIN_' . $permohonan->no_sk . '_' . Str::slug($permohonan->identitas->nama_lembaga, '_')  . '.pdf';
        
        // Return berdasarkan parameter request
        return $request->has('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    // Generate PDF Draft Sertifikat
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
        
        $filename = 'SERTIFIKAT_IZIN_' . $permohonan->no_sk . '_' . Str::slug($permohonan->identitas->nama_lembaga, '_')  . '.pdf';
    
        return $request->has('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    // Download SK
    public function downloadSKIzin($id)
    {        
        // Cari dokumen SK dari tabel lampiran
        $dokumen = Lampiran::where('permohonan_id', $id)
                        ->where('lampiran_type', 'sk_izin')
                        ->latest()
                        ->firstOrFail();
        
        // Return file untuk diunduh
        return redirect('storage/'.$dokumen->lampiran_path);
    }

    // Download Sertifikat
    public function downloadSertifikat($id)
    {
        // Cari dokumen SK dari tabel lampiran
        $dokumen = Lampiran::where('permohonan_id', $id)
                        ->where('lampiran_type', 'sertifikat_izin')
                        ->latest()
                        ->firstOrFail();

        // Return file untuk diunduh
        return redirect('storage/'.$dokumen->lampiran_path);
    }
    
    // Download Semua Dokumen to ZIP
    public function downloadAllDokumen(Permohonan $permohonan)
    {
        // Validasi permohonan memiliki lampiran
        if ($permohonan->lampiran->isEmpty()) {
            return back()->with('error', 'Tidak ada lampiran yang tersedia untuk diunduh.');
        }

        // Buat nama file zip unik
        $tanggalSekarang = now()->format('Ymd');
        $zipFileName = 'Dokumen-Permohonan-' . $permohonan->identitas->nama_lembaga . '-' . $tanggalSekarang . '.zip';
        $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
        $zipFilePath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;
        
        // Pastikan direktori temp ada
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Buat file zip baru
        $zip = new ZipArchive();
        $result = $zip->open($zipFilePath, ZipArchive::CREATE);
        
        if ($result !== true) {
            return back()->with('error', 'Gagal membuat file zip.');
        }

        $fileCount = $this->addFilesToZip($zip, $permohonan->lampiran);

        $zip->close();

        // Jika tidak ada file yang berhasil ditambahkan, hapus zip dan kembalikan error
        if ($fileCount === 0) {
            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
            }
            return back()->with('error', 'Tidak ada file lampiran yang ditemukan untuk diunduh.');
        }

        // Download file
        return response()->download($zipFilePath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    // Menambahkan file-file lampiran ke dalam zip
    private function addFilesToZip(ZipArchive $zip, $lampirans)
    {
        $fileCount = 0;

        foreach ($lampirans as $lampiran) {
            // Cari file di berbagai kemungkinan lokasi
            $fileData = $this->findFile($lampiran->lampiran_path);
            
            if ($fileData['found'] && $fileData['content']) {
                try {
                    $originalFileName = basename($lampiran->lampiran_path);
                    $displayFileName = $this->generateFileName($lampiran, $originalFileName);
                    
                    // Cek jika nama file sudah ada di zip
                    if ($zip->locateName($displayFileName) !== false) {
                        $pathInfo = pathinfo($displayFileName);
                        $displayFileName = $pathInfo['filename'] . '-' . Str::random(4) . '.' . ($pathInfo['extension'] ?? '');
                    }
                    
                    // Tambahkan file ke zip
                    $zip->addFromString($displayFileName, $fileData['content']);
                    $fileCount++;
                } catch (\Exception $e) {
                    // Gagal menambahkan file - lanjutkan ke file berikutnya
                    continue;
                }
            }
        }

        return $fileCount;
    }

    // Mencari file di berbagai kemungkinan lokasi
    private function findFile($path)
    {
        // Coba berbagai kemungkinan lokasi dengan Storage facade
        $possiblePaths = [
            $path,
            'public/' . $path,
            'public/storage/' . $path,
        ];
        
        foreach ($possiblePaths as $possiblePath) {
            if (Storage::exists($possiblePath)) {
                return [
                    'found' => true,
                    'content' => Storage::get($possiblePath)
                ];
            }
        }
        
        // Coba dengan direct filesystem access
        $directPaths = [
            public_path(str_replace('/', DIRECTORY_SEPARATOR, $path)),
            storage_path('app/' . str_replace('/', DIRECTORY_SEPARATOR, $path)),
            storage_path('app/public/' . str_replace('/', DIRECTORY_SEPARATOR, $path)),
            base_path('public/' . str_replace('/', DIRECTORY_SEPARATOR, $path)),
        ];
        
        foreach ($directPaths as $directPath) {
            if (file_exists($directPath)) {
                return [
                    'found' => true,
                    'content' => file_get_contents($directPath)
                ];
            }
        }
        
        return ['found' => false, 'content' => null];
    }

    // Generate nama file untuk tampilan di zip
    private function generateFileName($lampiran, $originalFileName)
    {
        return $lampiran->lampiran_type 
            ? Str::slug($lampiran->lampiran_type, '_') . '-' . $originalFileName 
            : $originalFileName;
    }
}