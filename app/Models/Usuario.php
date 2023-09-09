<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * CREATE TABLE usuarios (
 *     id integer NOT NULL,
 *     nombres_apellidos character varying(60) NOT NULL,
 *     fecha_nacimiento date NOT NULL,
 *     direccion character varying(50) NOT NULL,
 *     telefono character varying(30) NOT NULL,
 *     clave character(40) NOT NULL,
 *     correo character varying(50) NOT NULL,
 *     creado_en date,
 *     creado_por integer,
 *     actualizado_en date,
 *     actualizado_por integer,
 *     departamentos_id integer NOT NULL,
 *     estado_id integer NOT NULL,
 *     rol_id integer NOT NULL,
 *     agencia_id integer NOT NULL,
 *     imagen text,
 * );
 */
class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $fillable = [
        'nombres_apellidos',
        'fecha_nacimiento',
        'direccion',
        'telefono',
        'usuario',
        'clave',
        'correo',
        'estado_id',
        'rol_id',
        'agencia_id',
        'imagen',
    ];
    protected $casts = [
        'imagen' => 'array',
    ];
    protected $textSearchColumns = [
        'nombres_apellidos',
        'direccion',
        'telefono',
        'correo',
    ];
    protected $guarded = [
        'creado_por',
        'actualizado_por',
    ];
    protected $attributes = [
        'nombres_apellidos' => '',
        'fecha_nacimiento' => null,
        'direccion' => '',
        'telefono' => '',
        'usuario' => '',
        'clave' => '',
        'correo' => '',
        'estado_id' => 1,
        'rol_id' => 0,
        'cargo' => '',
        'agencia_id' => null,
        'imagen' => '',
    ];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    const ROL_ADMINISTRADOR = 1;
    const ROL_GERENTE = 2;
    const ROL_SUPERVISOR = 3;
    const ROL_AUDIDOR = 4;

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    // Set username and password fields
    public function getAuthPassword()
    {
        return $this->clave;
    }

    /**
     * Encrypt the password before saving it to the database.
     */
    public function setClaveAttribute($value)
    {
        $this->attributes['clave'] = bcrypt($value);
    }
}
