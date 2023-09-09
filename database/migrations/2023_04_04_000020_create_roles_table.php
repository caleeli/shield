<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CREATE TABLE roles (
 *   id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
 *   descripcion VARCHAR NULL,
 *   estado VARCHAR DEFAULT 'ACTIVO',
 *   creado_en DATETIME NULL,
 *   creado_por INTEGER UNSIGNED NULL,
 *   actualizado_en DATETIME NULL,
 *   actualiza_por INTEGER UNSIGNED NULL,
 *   PRIMARY KEY(rol_id),
 * );
 * 
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id');
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('estado_id')->default(1);
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
