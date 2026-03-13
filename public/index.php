<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Se o sistema estiver em manutenção, carrega a página de aviso.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// CARREGA O AUTOLOAD DO COMPOSER
// Gerencia todas as bibliotecas e classes instaladas no projeto.
require __DIR__.'/../vendor/autoload.php';

// INICIALIZA O LARAVEL
// Carrega o núcleo do sistema (bootstrap) e processa a requisição atual.
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
