<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioLog extends Model {
    protected $table = 'usuario_log';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'usuario_id',
        'created_at'
    ];

    public function usuario(): BelongsTo {
        return $this->belongsTo(Usuario::class);
    }
}
