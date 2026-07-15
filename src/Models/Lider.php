<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lider extends Model {
    protected $table = 'lider';
    protected $fillable = ['pessoa_id', 'gabinete_id', 'token', 'ativo'];

    // Retorna os dados cadastrais desta liderança
    public function pessoa() {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    // Retorna todos os relacionamentos que este líder gerou
    public function relacionamentos() {
        return $this->hasMany(PessoaRelacionamento::class, 'indicado_por_lider_id');
    }
}
