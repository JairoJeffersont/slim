<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaTecnica extends Model {
    protected $table = 'nota_tecnica';

    protected $fillable = [
        'gabinete_id',
        'usuario_id',
        'proposicao_id',
        'apelido',
        'resumo',
        'texto'
    ];

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }
}
