<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Emenda extends Model {

    protected $table = 'emenda';

    protected $fillable = [
        'numero',
        'estado',
        'municipio',
        'objeto',
        'valor_indicado',
        'valor_pago',
        'valor_executado',
        'informacao_adicional',
        'gabinete_id',
        'tipo_emenda_id',
        'tema_emenda_id',
        'situacao_emenda_id',
        'usuario_id'
    ];

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class, 'gabinete_id');
    }

    public function tipoEmenda(): BelongsTo {
        return $this->belongsTo(TipoEmenda::class, 'tipo_emenda_id');
    }

    public function temaEmenda(): BelongsTo {
        return $this->belongsTo(TemaEmenda::class, 'tema_emenda_id');
    }

    public function situacaoEmenda(): BelongsTo {
        return $this->belongsTo(SituacaoEmenda::class, 'situacao_emenda_id');
    }
}
