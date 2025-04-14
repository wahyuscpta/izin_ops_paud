<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramPendidikan extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'permohonan_id',
        'bahan_pembelajaran',
        'cara_penyampaian'
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'bahan_pembelajaran' => 'array',
        'cara_penyampaian' => 'array'
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }
}
