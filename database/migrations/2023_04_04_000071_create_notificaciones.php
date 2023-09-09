<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de notificaciones para usuarios con columnas como:
 *     - usuario_id
 *     - texto
 *     - enlace
 *     - leido
 *     - creado_en
 *     - actualizado_en
 *     - creado_por
 *     - actualizado_por
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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->text('icono');
            $table->text('texto');
            $table->text('enlace');
            $table->boolean('leido')->default(false);
            $table->unsignedBigInteger('proyecto_id')->nullable();
            $table->string('request_id', 255)->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('creado_por')->references('id')->on('usuarios');
            $table->foreign('actualizado_por')->references('id')->on('usuarios');
            $table->foreign('proyecto_id')->references('id')->on('proyectos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
};
