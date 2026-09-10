<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->tinyInteger('nivel')->default(1);
        });
        Schema::create('casas', function (Blueprint $table) {
            $table->id();
            $table->string('casa', 50)->unique();
            $table->string('direccion', 255);
            $table->string('gmaps', 255);
            $table->string('ubicacion', 60)->nullable();
            $table->json('adicionales')->nullable();
        });
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('cuenta', 20)->unique();
            $table->string('nombre', 255);
            $table->json('adicionales')->nullable();
        });
        Schema::create('cuartos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdCasa')->constrained('casas')->cascadeOnDelete();
            $table->smallInteger('cuarto');
            $table->enum('estatus', ['disponible', 'ocupado', 'mantenimiento'])->default('disponible');
            $table->json('adicionales')->nullable();
        });
        Schema::create('invMuebles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdCuarto')->constrained('cuartos')->cascadeOnDelete();
            $table->string('mueble',50);
            $table->enum('estatus', ['bueno', 'dañado', 'perdido','falta'])->default('bueno');
            $table->json('adicionales')->nullable();
        });
        Schema::create('inquilinos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdUser')->constrained('users')->restrictOnDelete()->default(6);
            $table->string('inquilino', 200);
            $table->string('telefono', 10);
            $table->text('generales')->nullable();
            $table->json('adicionales')->nullable();
        });
        Schema::create('propietarios', function (Blueprint $table) {
            $table->id();
            $table->string('propietario', 200);
            $table->text('generales')->nullable();
            $table->json('adicionales')->nullable();
        });
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('vehiculo', 20);
            $table->string('numero', 10);
            $table->enum('estatus', ['disponible', 'ocupado', 'mantenimiento'])->default('disponible');
            $table->json('adicionales')->nullable();
        });
        Schema::create('tecnicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdUser')->constrained('users')->restrictOnDelete();
            $table->foreignId('IdVehiculo')->constrained('vehiculos')->restrictOnDelete();
            $table->string('tecnico', 100);
            $table->string('telefono', 10);
            $table->boolean('activo')->default(true);
            $table->json('adicionales')->nullable();
        });
        Schema::create('asignacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdCasa')->constrained('casas')->restrictOnDelete();
            $table->foreignId('IdUser')->constrained('users')->restrictOnDelete();
        });
        Schema::create('prioridads', function (Blueprint $table) {
            $table->id();
            $table->string('prioridad', 20);
            $table->integer('diasTolerancia');
            $table->string('colorHex', 7);
        });
        Schema::create('fallas', function (Blueprint $table) {
            $table->id();
            $table->string('falla', 100);
        });
        Schema::create('penas', function (Blueprint $table) {
            $table->id();
            $table->string('pena', 20);
            $table->decimal('descuentoDias', 5,2);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fallas');
        Schema::dropIfExists('prioridads');
        Schema::dropIfExists('asignaciones');
        Schema::dropIfExists('tecnicos');
        Schema::dropIfExists('vehiculos');
        Schema::dropIfExists('inquilinos');
        Schema::dropIfExists('cuartos');
        Schema::dropIfExists('casas');
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('nivel');
        });
    }
};