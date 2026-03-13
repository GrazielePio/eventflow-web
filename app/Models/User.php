<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * MODEL USUÁRIO
 * Responsável por representar a tabela 'users' no banco de dados.
 */
class User extends Authenticatable
{
    /**
     * Essencial para que o sistema mostre apenas os eventos do dono da conta.
     */
    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }
    
    // Permite que o usuário gere tokens de acesso (Sanctum).
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Define quais campos o Laravel permite salvar via formulário por segurança.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Garante que a senha nunca seja enviada nas respostas JSON da API.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * CONVERSÃO DE DADOS (Casts)
     * Garante que a senha seja tratada como 'hash' e datas como objetos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}