<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends Model {

    protected $table = 'tipo_documento';

    protected $fillable = [
        'nome',
        'sigla',
        'gabinete_id',
        'usuario_id'
    ];

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

}
