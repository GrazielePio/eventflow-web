<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * SERVICE PROVIDER GLOBAL
 * Este arquivo é responsável por gerenciar a inicialização (bootstrap) de 
 * todos os serviços do sistema Laravel.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * REGISTRO DE SERVIÇOS
     * Usado para vincular interfaces a implementações no container de serviços.
     */
    public function register(): void
    {
        //
    }

    /**
     * INICIALIZAÇÃO (BOOTSTRAP)
     * Executado após todos os serviços serem registrados. É usado para 
     * configurar detalhes globais, como padrões de datas ou esquemas de banco.
     */
    public function boot(): void
    {
        //
    }
}
