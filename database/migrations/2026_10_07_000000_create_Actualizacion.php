<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up()
    {
        Schema::create('expedientesDocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdExpedienteDet')->constrained('expedientesDets')->cascadeOnDelete();
            $table->enum('tipo', ['promocion', 'acuerdo', 'anexo']);
            $table->string('archivo');
            $table->json('adicionales')->nullable();
        });
    }    
};