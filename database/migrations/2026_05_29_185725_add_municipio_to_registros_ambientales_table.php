<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('registros_ambientales', function (Blueprint $table) {
            // Agregamos la columna municipio después de longitud.
            // Nullable por si algún celular falla en obtener el nombre.
            $table->string('municipio')->nullable()->after('longitud');
        });
    }

    public function down()
    {
        Schema::table('registros_ambientales', function (Blueprint $table) {
            $table->dropColumn('municipio');
        });
    }
};
