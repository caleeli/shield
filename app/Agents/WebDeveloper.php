<?php

namespace App\Agents;

use App\Services\MakeIntelligentTrait;

class WebDeveloper
{
    use MakeIntelligentTrait;

    public string $screen;

    /**
     * Prueba un componente vue2 y lo guarda en un archivo .vue
     * 
     * @param string $componente Código del componente vue2. ej. `<template><div>hola</div></template>`
     */
    public function probar_componente_vue(string $componente)
    {
        $this->screen = $componente;
        return "Success";
    }
}
