<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoUsuario extends Model {
    protected $table = 'tipo_usuario';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'nome'
    ];

    public function usuarios(): HasMany {
        return $this->hasMany(Usuario::class);
    }
}
