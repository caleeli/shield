<?php

namespace App\Models;

/**
 * Tabla de notificaciones para usuarios con columnas como:
 *     - usuario_id
 *     - texto
 *     - leido
 *     - creado_en
 *     - actualizado_en
 *     - creado_por
 *     - actualizado_por
 */
class Notificacion extends Model
{
    protected $table = 'notificaciones';
    public $timestamps = true;
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    protected $attributes = [
        'icono' => 'fas fa-bell',
        'texto' => '',
        'enlace' => '',
        'leido' => false,
        'creado_por' => null,
        'actualizado_por' => null,
        'proyecto_id' => null,
        'request_id' => null,
    ];
    protected $fillable = [
        'usuario_id',
        'icono',
        'texto',
        'enlace',
        'leido',
        'proyecto_id',
        'request_id',
    ];
    protected $textSearchColumns = [
        'texto'
    ];
    protected $casts = [
        'attributes' => 'array'
    ];
    protected $guarded = [
        'creado_por',
        'actualizado_por'
    ];
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
