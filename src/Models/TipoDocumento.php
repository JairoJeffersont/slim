<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoDocumento extends Model {
    protected $table = 'tipo_documento';

    protected $fillable = [
        'nome',
        'sigla',
        'usuario_id',
        'gabinete_id'
    ];

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

}
