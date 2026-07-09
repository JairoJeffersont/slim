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
        'nome',
        'cidade',
        'estado',
        'ativo',
        'tipo_gabinete_id'
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

    public function tiposEmendas(): HasMany {
        return $this->hasMany(TipoEmenda::class);
    }

    public function temasEmendas(): HasMany {
        return $this->hasMany(TemaEmenda::class);
    }

    public function situacoesEmendas(): HasMany {
        return $this->hasMany(SituacaoEmenda::class);
    }

    public function emendas(): HasMany {
        return $this->hasMany(Emenda::class);
    }

    public function notasTecnicas(): HasMany {
        return $this->hasMany(NotaTecnica::class);
    }

    public function orgaos(): HasMany {
        return $this->hasMany(Orgao::class);
    }

    public function profissoes(): HasMany {
        return $this->hasMany(Profissao::class);
    }

    public function pessoas(): HasMany {
        return $this->hasMany(Pessoa::class);
    }

    public function tiposDocumentos(): HasMany {
        return $this->hasMany(TipoDocumento::class);
    }

    public function documentos(): HasMany {
        return $this->hasMany(Documento::class);
    }
}
