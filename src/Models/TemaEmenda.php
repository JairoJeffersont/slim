<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemaEmenda extends Model {
    protected $table = 'tema_emenda';

    protected $fillable = [
        'nome',
        'gabinete_id',
        'usuario_id'
    ];

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

    public function emendas(): HasMany {
        return $this->hasMany(Emenda::class, 'tema_emenda_id');
    }
}
