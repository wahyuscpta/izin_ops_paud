<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengelola extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'permohonan_id',
        'nama_pengelola',
        'agama_pengelola',
        'jenis_kelamin_pengelola',
        'kewarganegaraan_pengelola',
        'ktp_pengelola',
        'tanggal_pengelola',
        'alamat_pengelola',
        'telepon_pengelola',
        'kabupaten_pengelola',
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'kabupaten_pengelola', 'id');
    }
}
