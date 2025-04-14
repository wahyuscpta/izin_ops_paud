<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Permohonan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [        
        'user_id',
        'no_permohonan',
        'tgl_permohonan',
        'tgl_status_terakhir',
        'catatan',
        'status_permohonan'
    ];
    
    protected $guarded = ['id'];
    
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'tgl_permohonan' => 'datetime',
        'tgl_status_terakhir' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function identitas(): HasOne
    {
        return $this->hasOne(Identitas::class);
    }

    public function penyelenggara(): HasOne
    {
        return $this->hasOne(Penyelenggara::class);
    }

    public function pengelola(): HasOne
    {
        return $this->hasOne(Pengelola::class);
    }

    public function peserta_didik(): HasOne
    {
        return $this->hasOne(PesertaDidik::class);
    }

    public function personalia(): HasOne
    {
        return $this->hasOne(Personalia::class);
    }

    public function program_pendidikan(): HasOne
    {
        return $this->hasOne(ProgramPendidikan::class);
    }

    public function prasarana(): HasOne
    {
        return $this->hasOne(Prasarana::class);
    }

    public function sarana(): HasOne
    {
        return $this->hasOne(Sarana::class);
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(Lampiran::class);
    }
}
