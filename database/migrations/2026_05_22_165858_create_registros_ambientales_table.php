<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('registros_ambientales', function (Blueprint $table) {
            $table->id();
            // El UUID es crucial para evitar duplicados al sincronizar
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->date('fecha');
            $table->dateTime('timestamp');
            $table->string('eje');
            $table->string('categoria');
            $table->string('subcategoria')->nullable();
            $table->decimal('cantidad', 10, 2);
            $table->text('observaciones')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->string('fotoPath')->nullable(); // Guardará la ruta del servidor, no la del teléfono

            $table->timestamps(); // Crea created_at y updated_at de Laravel
        });
    }

    public function down()
    {
        Schema::dropIfExists('registros_ambientales');
    }
};
