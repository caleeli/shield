<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * CREATE TABLE usuarios (
 *   id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
 *   nombres_apellidos VARCHAR NOT NULL,
 *   fecha_nacimiento DATE NULL,
 *   direccion VARCHAR NULL,
 *   telefono VARCHAR NULL,
 *   clave CHAR(60) NULL,
 *   estado_id INTEGER UNSIGNED NULL, -- from estados
 *   usuario VARCHAR NULL,
 *   correo VARCHAR NULL,
 *   departamento_id INTEGER UNSIGNED NULL, -- from departamentos
 *   agencia_id INTEGER UNSIGNED NULL, -- from agencias
 *   rol_id INTEGER UNSIGNED NULL, -- from roles
 *   creado_en DATETIME NULL,
 *   creado_por INTEGER UNSIGNED NULL, -- from usuarios
 *   actualizado_en DATETIME NULL,
 *   actualizado_por INTEGER UNSIGNED NULL, -- from usuarios
 *   PRIMARY KEY(usuario_id)
 * );
 *
 */
class UsuariosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('usuarios')->insert([
            'nombres_apellidos' => 'Admin',
            'fecha_nacimiento' => '1992-12-25',
            'direccion' => 'Calle falsa 123',
            'telefono' => '1234567890',
            'clave' => Hash::make('123456'),
            'estado_id' => 1,
            'usuario' => 'admin',
            'correo' => 'admin@example.com',
            'imagen' => json_encode(['url' => '/avatars/avatar1.png']),
            'departamento_id' => 1,
            'agencia_id' => 1,
            'rol_id' => 1,
            'creado_en' => now(),
            'creado_por' => 1,
            'actualizado_en' => now(),
            'actualizado_por' => 1,
        ]);
    }
}
