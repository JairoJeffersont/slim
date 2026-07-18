<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agenda extends Model {

    protected $table = 'agenda';

    protected $fillable = [
        'gabinete_id',
        'tipo_agenda_id',
        'situacao_agenda_id',
        'usuario_id',
        'pessoa_id',
        'titulo',
        'descricao',
        'local',
        'data_hora',
        'data_hora_fim'
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'data_hora_fim' => 'datetime'
    ];

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class, 'gabinete_id');
    }

    public function tipoAgenda(): BelongsTo {
        return $this->belongsTo(TipoAgenda::class, 'tipo_agenda_id');
    }

    public function situacaoAgenda(): BelongsTo {
        return $this->belongsTo(SituacaoAgenda::class, 'situacao_agenda_id');
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pessoa(): BelongsTo {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
