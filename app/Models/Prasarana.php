<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prasarana extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'permohonan_id',
        'ruang_belajar',
        'ruang_bermain',
        'ruang_pimpinan',
        'ruang_sumber_belajar',
        'ruang_guru',
        'ruang_tata_usaha',
        'kamar_mandi',
        'kamar_kecil',
    ];

    protected $casts = [
        'ruang_belajar' => 'array',
        'ruang_bermain' => 'array',
        'ruang_pimpinan' => 'array',
        'ruang_sumber_belajar' => 'array',
        'ruang_guru' => 'array',
        'ruang_tata_usaha' => 'array',
        'kamar_mandi' => 'array',
        'kamar_kecil' => 'array',
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }
}
