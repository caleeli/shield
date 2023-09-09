<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use ProcessMaker\Laravel\Facades\Nayra;
use Tests\TestCase;

class BpmnAITest extends TestCase
{
    use RefreshDatabase;

    public function testBpmnReporte()
    {
        $data = [
            'message' => 'Reporte para mostrar una lista de usuarios, debe tener un filtro por estado.',
        ];
        $instance = Nayra::startProcess('bpmn/Reportes.bpmn', '_2', $data);
        $data = $instance->getDataStore()->getData();
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('query', $data);
        $this->assertArrayHasKey('response', $data);
    }
}
