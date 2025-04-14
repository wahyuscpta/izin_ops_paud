<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sarana extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'permohonan_id',            
        'buku_pelajaran',
        'alat_permainan_edukatif',
        'meja_kursi',
        'papan_tulis',
        'alat_tata_usaha',
        'listrik',
        'air_bersih',
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }
}
