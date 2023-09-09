<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

/**
 * CREATE TABLE roles (
 *   id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
 *   descripcion VARCHAR NULL,
 *   estado VARCHAR DEFAULT 'ACTIVO',
 *   creado_en DATETIME NULL,
 *   creado_por INTEGER UNSIGNED NULL,
 *   actualizado_en DATETIME NULL,
 *   actualizado_por INTEGER UNSIGNED NULL,
 *   PRIMARY KEY(rol_id),
 * );
 * 
 */
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'descripcion' => 'Administrador',
                'estado_id' => 1,
                'creado_en' => now(),
                'creado_por' => 1,
                'actualizado_en' => now(),
                'actualizado_por' => 1
            ],
            [
                'descripcion' => 'Gerente Auditoria',
                'estado_id' => 1,
                'creado_en' => now(),
                'creado_por' => 1,
                'actualizado_en' => now(),
                'actualizado_por' => 1
            ],
            [
                'descripcion' => 'Supervisor',
                'estado_id' => 1,
                'creado_en' => now(),
                'creado_por' => 1,
                'actualizado_en' => now(),
                'actualizado_por' => 1
            ],
            [
                'descripcion' => 'Auditor',
                'estado_id' => 1,
                'creado_en' => now(),
                'creado_por' => 1,
                'actualizado_en' => now(),
                'actualizado_por' => 1
            ],
        ]);
    }
}

