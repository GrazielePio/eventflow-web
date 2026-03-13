<?php

use Illuminate\Support\Facades\Route;

// Esta rota captura QUALQUER URL e entrega o app Vue
// O Vue Router ou seus componentes decidem o que mostrar
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');