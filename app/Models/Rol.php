<?php
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


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'descripcion',
        'estado',
        'creado_en',
        'creado_por',
        'actualizado_en',
        'actualiza_por',
    ];

    public $timestamps = false;

    const CREATED_AT="creado_en";
    const UPDATED_AT="actualizado_en";

    public const ADMIN = 1;
    public const GERENTE = 2;
    public const SUPERVISOR = 3;
    public const AUDITOR = 4;
}
