<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a tabela 'eventos'
     */
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titulo', 255);
            $table->date('data');
            $table->string('local', 255)->nullable(); // Adicione o nullable() aqui
            $table->text('descricao')->nullable();
            
            $table->string('cep', 9);
            $table->string('logradouro');
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado', 2);
            $table->enum('categoria', ['Workshop', 'Palestra', 'Show', 'Outros']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};