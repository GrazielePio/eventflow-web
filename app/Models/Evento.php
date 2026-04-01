<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'titulo', 
        'data', 
        'categoria', 
        'cep', 
        'logradouro', 
        'bairro', 
        'cidade', 
        'estado', 
        'local', 
        'descricao',
        'foto',        // ADICIONADO
        'manual_pdf'   // ADICIONADO
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}