<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penyelenggara extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'permohonan_id',
        'nama_perorangan',
        'agama_perorangan',
        'kewarganegaraan_perorangan',
        'ktp_perorangan',
        'tanggal_perorangan',
        'alamat_perorangan',
        'telepon_perorangan',
        'kabupaten_perorangan',
        'nama_badan',
        'agama_badan',
        'akte_badan',
        'nomor_badan',
        'tanggal_badan',
        'alamat_badan',
        'telepon_badan',
        'kabupaten_badan',
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function regencyPerorangan()
    {
        return $this->belongsTo(Regency::class, 'kabupaten_perorangan', 'id');
    }

    public function regencyBadan()
    {
        return $this->belongsTo(Regency::class, 'kabupaten_badan', 'id');
    }
}
