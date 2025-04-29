<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PermohonanExportController extends Controller
{
    public function export(Request $request, $id)
    {
        $permohonan = Permohonan::with(['identitas', 'user'])->findOrFail($id);

        $pdf = Pdf::loadView('filament.permohonan.pdf-permohonan', compact('permohonan'))->setPaper('A4', 'portrait');

        return $request->has('download')
            ? $pdf->download('permohonan-' . $permohonan->id . '.pdf')
            : $pdf->stream('permohonan-' . $permohonan->id . '.pdf');
    }
}
