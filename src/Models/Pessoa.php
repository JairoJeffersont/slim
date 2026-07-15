<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pessoa extends Model {

    protected $table = 'pessoa';

    protected $fillable = [
        'nome',
        'orgao_id',
        'profissao_id',
        'email',
        'telefone',
        'aniversario',
        'endereco',
        'bairro',
        'cidade',
        'estado',
        'instagram',
        'facebook',
        'foto',
        'informacoes',
        'gabinete_id',
        'usuario_id'
    ];

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }

    public function orgao(): BelongsTo {
        return $this->belongsTo(Orgao::class);
    }

    public function profissao(): BelongsTo {
        return $this->belongsTo(Profissao::class);
    }


}
