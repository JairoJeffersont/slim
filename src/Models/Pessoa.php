<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pessoa extends Model {

    protected $table = 'pessoa';

    protected $fillable = [
        'gabinete_id',
        'orgao_id',
        'profissao_id',
        'indicado_por_pessoa_id',
        'nome',
        'sexo',
        'token',
        'aniversario',
        'cpf',
        'telefone',
        'email',
        'instagram',
        'foto',
        'facebook',
        'endereco',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'lideranca',
        'observacao'
    ];

    protected $casts = [
        'aniversario' => 'date',
        'lideranca' => 'boolean',
    ];

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

    public function orgao(): BelongsTo {
        return $this->belongsTo(Orgao::class);
    }

    public function profissao(): BelongsTo {
        return $this->belongsTo(Profissao::class);
    }

    public function indicadoPor(): BelongsTo {
        return $this->belongsTo(Pessoa::class, 'indicado_por_pessoa_id');
    }

    public function indicados(): HasMany {
        return $this->hasMany(Pessoa::class, 'indicado_por_pessoa_id');
    }
}
