<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoaRelacionamento extends Model {
    protected $table = 'pessoa_relacionamento';
    public $timestamps = false; // Como só temos created_at na tabela
    protected $fillable = ['liderado_id', 'indicado_por_lider_id', 'gabinete_id'];

    // Retorna o cadastro da pessoa que foi liderada/indicada
    public function liderado() {
        return $this->belongsTo(Pessoa::class, 'liderado_id');
    }

    // Retorna a entidade líder que fez a indicação
    public function lider() {
        return $this->belongsTo(Lider::class, 'indicado_por_lider_id');
    }
}
