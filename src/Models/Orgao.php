<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orgao extends Model {

    protected $table = 'orgao';

    protected $fillable = [
        'gabinete_id',
        'usuario_id',
        'tipo_orgao_id',
        'nome',
        'cep',
        'bairro',
        'telefone',
        'email',
        'endereco',
        'cidade',
        'estado',
        'informacoes'
    ];

    public function tipoOrgao(): BelongsTo {
        return $this->belongsTo(TipoOrgao::class);
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

    public function pessoas(): HasMany {
        return $this->hasMany(Pessoa::class);
    }
}
