<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Evento
 * Representa um evento no sistema EventFlow
 */
class Evento extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'user_id',
        'titulo',
        'data',
        'local',
        'descricao',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'data' => 'date:Y-m-d',
    ];

    /**
     * Relacionamento: Um evento pertence a um usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}