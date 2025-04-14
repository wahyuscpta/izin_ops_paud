<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cabang extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'identitas_id',
        'nama_lembaga_cabang',
        'alamat_lembaga_cabang'
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function identitas(): BelongsTo
    {
        return $this->belongsTo(Identitas::class);
    }
}
