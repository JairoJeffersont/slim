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
        'lideranca',
        'token',
        'indicado_por_pessoa_id',
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

    public function agendas(): HasMany {
        return $this->hasMany('App\\Models\\Agenda', 'pessoa_id');
    }
    public function indicador(): BelongsTo {
        return $this->belongsTo(Pessoa::class, 'indicado_por_pessoa_id');
    }

    public function liderados(): HasMany {
        return $this->hasMany(Pessoa::class, 'indicado_por_pessoa_id');
    }
}
