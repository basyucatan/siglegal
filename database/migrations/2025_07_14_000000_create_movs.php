<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('expediente');
            $table->foreignId('IdOrgano')->nullable()->constrained('organos')->nullOnDelete();
            $table->foreignId('IdActor')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('IdDemandado')->nullable()->constrained('personas')->restrictOnDelete();
            $table->string('asunto')->nullable();
            $table->json('adicionales')->nullable();
            $table->timestamps();
        });
        Schema::create('expedientesDets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdExpediente')->constrained('expedientes')->cascadeOnDelete();
            $table->string('descripcion');
            $table->date('fechaPre');
            $table->date('fechaAcu')->nullable();
            $table->date('fechaPub')->nullable();
            $table->string('docPromocion')->nullable();
            $table->string('docAnexo')->nullable();
            $table->string('docAcuerdo')->nullable();
            $table->json('adicionales')->nullable();
        });
        Schema::create('expedientesPends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdExpediente')->constrained('expedientes')->cascadeOnDelete();
            $table->string('pendiente');
            $table->date('fechaPro');
            $table->date('fechaCum')->nullable();
            $table->json('adicionales')->nullable();
        });        
    }
 

    public function down()
    {
        Schema::dropIfExists('expedientes');
        Schema::dropIfExists('expedientesDets');
        Schema::dropIfExists('expedientesPends');
    }
};

