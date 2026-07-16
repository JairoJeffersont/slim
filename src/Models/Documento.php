<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model {

    protected $table = 'documento';

    protected $fillable = [
        'gabinete_id',
        'tipo_documento_id',
        'usuario_id',
        'titulo',
        'resumo',
        'numero',
        'ano',
        'arquivo_url'
    ];


    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class, 'gabinete_id');
    }

    public function tipo(): BelongsTo {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
