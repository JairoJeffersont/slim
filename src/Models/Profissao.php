<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profissao extends Model {
    protected $table = 'profissao';

    protected $fillable = [
        'nome',
        'usuario_id',
        'gabinete_id'
    ];

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
