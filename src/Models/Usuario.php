<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model {

    protected $table = 'usuario';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'token',
        'senha',
        'aniversario',
        'ativo',
        'tipo_usuario_id',
        'gabinete_id'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'aniversario' => 'date'
    ];

    protected $hidden = [
        'senha'
    ];

    public function tipoUsuario(): BelongsTo {
        return $this->belongsTo(TipoUsuario::class);
    }

    public function gabinete(): BelongsTo {
        return $this->belongsTo(Gabinete::class);
    }

    public function logs(): HasMany {
        return $this->hasMany(UsuarioLog::class)->orderBy('created_at', 'desc');
    }

    public function tiposOrgaos(): HasMany {
        return $this->hasMany(TipoOrgao::class);
    }

    public function profissoes(): HasMany {
        return $this->hasMany(Profissao::class);
    }

    public function orgaos(): HasMany {
        return $this->hasMany(Orgao::class);
    }

    public function pessoas(): HasMany {
        return $this->hasMany(Pessoa::class);
    }

    public function tiposDocumentos(): HasMany {
        return $this->hasMany(TipoDocumento::class);
    }
}
