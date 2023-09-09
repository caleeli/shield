<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CREATE TABLE public.usuarios (
 *   id                 bigserial NOT NULL,
 *   nombres_apellidos  varchar(255) NOT NULL,
 *   fecha_nacimiento   date,
 *   direccion          varchar(255),
 *   telefono           varchar(255),
 *   clave              char(60),
 *   estado_id          bigint NOT NULL DEFAULT '1'::bigint,
 *   usuario            varchar(255) NOT NULL,
 *   correo             varchar(255),
 *   imagen             text,
 *   departamento_id    bigint,
 *   agencia_id         bigint,
 *   rol_id             bigint,
 *   creado_en          timestamp without time zone,
 *   creado_por         bigint,
 *   actualizado_en     timestamp without time zone,
 *   actualizado_por    bigint,
 *   cargo              varchar(50),
 *   CONSTRAINT usuarios_pkey
 *     PRIMARY KEY (id)
 * );
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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id');
            $table->string('nombres_apellidos', 255)->notNull();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 255)->nullable();
            $table->char('clave', 60)->nullable();
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->string('usuario', 255)->notNull();
            $table->string('correo', 255)->nullable();
            $table->text('imagen')->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->unsignedBigInteger('agencia_id')->nullable();
            $table->unsignedBigInteger('rol_id')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->string('cargo', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
};
