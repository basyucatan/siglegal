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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('persona')->unique();
            $table->json('generales')->nullable();
            $table->json('adicionales')->nullable();
        });
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('materia');
            $table->json('adicionales')->nullable();
        });        
        Schema::create('organos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('IdMateria')->nullable()->constrained('materias')->nullOnDelete();
            $table->string('organo')->unique();
            $table->json('adicionales')->nullable();
        });
    }
 

    public function down()
    {
        Schema::dropIfExists('materias');
        Schema::dropIfExists('nombres');
        Schema::dropIfExists('organos');
    }
};
