<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SituacaoEmenda extends Model {

    protected $table = 'situacao_emenda';

    protected $fillable = [
        'nome',
        'usuario_id',
        'gabinete_id'
    ];

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class, 'gabinete_id');
    }

    public function emendas(): HasMany {
        return $this->hasMany(Emenda::class, 'situacao_emenda_id');
    }
}
