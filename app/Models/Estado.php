<?php

namespace App\Models;

/**
 * CREATE TABLE public.estados (
 *    id               integer NOT NULL PRIMARY KEY,
 *    descripcion      varchar(50) NOT NULL,
 *    estado           integer NOT NULL,
 *    creado_en        timestamp with time zone,
 *    creado_por       integer,
 *    actualizado_en   timestamp with time zone,
 *    actualizado_por  integer,
 *    tipo             integer,
 *    CONSTRAINT estados_pkey
 *      PRIMARY KEY (id)
 *  );
 */
class Estado extends Model
{
    protected $table = 'estados';
    public $timestamps = true;
    protected $fillable = [
        'descripcion',
        'estado',
        'tipo',
    ];
  
    protected $textSearchColumns = [
        'descripcion',
    ];
    protected $attributes = [
        'descripcion' => '',
        'estado' => 0,
        'tipo' => 0,
    ];
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    protected $guarded = [
        'creado_por',
        'actualizado_por',
    ];
}
