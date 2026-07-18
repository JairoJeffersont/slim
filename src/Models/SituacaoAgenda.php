<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SituacaoAgenda extends Model {

    protected $table = 'situacao_agenda';

    protected $fillable = [
        'nome',
        'gabinete_id',
        'usuario_id'
    ];

    /**
     * Relacionamento com o Gabinete
     */
    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

    /**
     * Relacionamento com o Usuário
     */
    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }
}
