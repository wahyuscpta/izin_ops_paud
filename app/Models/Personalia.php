<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personalia extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'permohonan_id',
        'guru_wni_lk',
        'guru_wni_pr',
        'guru_wni_jumlah',
        'asisten_wni_lk',
        'asisten_wni_pr',
        'asisten_wni_jumlah',
        'tata_usaha_wni_lk',
        'tata_usaha_wni_pr',
        'tata_usaha_wni_jumlah',
        'pesuruh_wni_lk',
        'pesuruh_wni_pr',
        'pesuruh_wni_jumlah',
        'guru_wna_lk',
        'guru_wna_pr',
        'guru_wna_jumlah',
        'asisten_wna_lk',
        'asisten_wna_pr',
        'asisten_wna_jumlah',
        'tata_usaha_wna_lk',
        'tata_usaha_wna_pr',
        'tata_usaha_wna_jumlah',
        'pesuruh_wna_lk',
        'pesuruh_wna_pr',
        'pesuruh_wna_jumlah',
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }
}
