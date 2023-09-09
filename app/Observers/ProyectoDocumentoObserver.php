<?php

namespace App\Observers;

use App\Models\ProyectoDocumento;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProyectoDocumentoObserver
{

    /**
     * Handle the ProyectoDocumento "created" event.
     */
    public function created(ProyectoDocumento $proyectoDocumento): void
    {

    }

    /**
     * Handle the ProyectoDocumento "updated" event.
     */
    public function updated(ProyectoDocumento $proyectoDocumento): void
    {
        //
    }

    /**
     * Handle the ProyectoDocumento "deleted" event.
     */
    public function deleted(ProyectoDocumento $proyectoDocumento): void
    {
        //
    }

    /**
     * Handle the ProyectoDocumento "restored" event.
     */
    public function restored(ProyectoDocumento $proyectoDocumento): void
    {
        //
    }

    /**
     * Handle the ProyectoDocumento "force deleted" event.
     */
    public function forceDeleted(ProyectoDocumento $proyectoDocumento): void
    {
        //
    }
}
