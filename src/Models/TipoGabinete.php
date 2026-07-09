<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoGabinete extends Model {
    protected $table = 'tipo_gabinete';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'nome'
    ];

    public function gabinetes(): HasMany {
        return $this->hasMany(Gabinete::class);
    }
}
