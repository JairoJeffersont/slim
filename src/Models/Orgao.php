<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Orgao extends Model {

    protected $table = 'orgao';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'endereco',
        'bairro',
        'cidade',
        'estado',
        'informacoes',
        'tipo_orgao_id',
        'usuario_id',
        'gabinete_id'
    ];

    public function tipoOrgao(): BelongsTo {
        return $this->belongsTo(TipoOrgao::class, 'tipo_orgao_id');
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }
}
