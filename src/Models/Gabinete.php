<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gabinete extends Model {

    protected $table = 'gabinete';

    protected $fillable = [
        'id_parlamentar',
        'token',
        'partido',
        'nome',
        'cidade',
        'estado',
        'ativo',
        'tipo_gabinete_id',
        'assinaturas'
    ];

    protected $casts = [
        'ativo' => 'boolean'
    ];

    public function tipoGabinete(): BelongsTo {
        return $this->belongsTo(TipoGabinete::class);
    }

    public function usuarios(): HasMany {
        return $this->hasMany(Usuario::class);
    }

    public function tiposOrgaos(): HasMany {
        return $this->hasMany(TipoOrgao::class);
    }

    public function orgaos(): HasMany {
        return $this->hasMany(Orgao::class);
    }


}
