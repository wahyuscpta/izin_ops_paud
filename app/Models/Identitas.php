<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Identitas extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'permohonan_id',
        'nama_lembaga',
        'alamat_identitas',
        'kabupaten_identitas',
        'kecamatan_identitas',
        'desa_identitas',
        'no_telepon_identitas',
        'tgl_didirikan',
        'tgl_terdaftar',
        'no_registrasi',
        'no_surat_keputusan',
        'rumpun_pendidikan',
        'jenis_pendidikan',
        'jenis_lembaga',
        'has_cabang',
        'jumlah_cabang',
        'nama_lembaga_induk',
        'alamat_lembaga_induk'
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'desa_identitas', 'id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'kecamatan_identitas', 'id');
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'kabupaten_identitas', 'id');
    }

    public function cabangs(): HasMany
    {
        return $this->hasMany(Cabang::class);
    }
}
