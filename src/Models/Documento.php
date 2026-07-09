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
        'descricao',
        'titulo',
        'resumo',
        'numero',
        'ano',
        'arquivo_url'
    ];

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

    public function tipoDocumento(): BelongsTo {
        return $this->belongsTo(TipoDocumento::class);
    }
}
