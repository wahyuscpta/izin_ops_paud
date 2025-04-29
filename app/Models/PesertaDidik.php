<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaDidik extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'permohonan_id',
        'jalur_penerimaan_tes',
        'tata_usaha_penerimaan',
        'jumlah_tiap_angkatan',
        'jumlah_menyelesaikan',
        'jumlah_sekarang_lk',
        'jumlah_sekarang_pr',
        'jumlah_sekarang_total',
        'jumlah_tamat_lk',
        'jumlah_tamat_pr',
        'jumlah_tamat_total',
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }
}
