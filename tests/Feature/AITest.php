<?php

namespace Tests\Feature;

use App\Services\AIService;
use App\Services\MakeIntelligentTrait;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AITest extends TestCase
{
    use RefreshDatabase;

    public function testComplete()
    {
        $service = new AIService(env('OPENAI_API_KEY'));
        $result = $service->complete('La capital oficial de {{variable}} es', ['variable' => 'Bolivia']);
        $this->assertIsString($result);
        $validAnswers = ['Sucre', 'La Paz'];
        // check if result contains any of the valid answers
        $valid = false;
        foreach($validAnswers as $answer) {
            $valid = $valid || strpos($result, $answer) !==false;
        }
        $this->assertTrue($valid);
    }

    public function testChatFunction()
    {
        $service = new AIService(env('OPENAI_API_KEY'));
        $chat = [
            [
                'role' => 'user',
                'content' => "¿Cómo está el clima en La Paz?"
            ],
        ];
        $functions = [
            [
                "name" => "get_current_weather",
                "description" => "Obtiene el clima actual en una ubicación determinada",
                "parameters" => [
                    "type" => "object",
                    "properties" => (object) [
                        "location" => [
                            "type" => "string",
                            "description" => "La ciudad y el estado, ej. San Francisco, Cochabamba"
                        ],
                        "unit" => [
                            "type" => "string",
                            "enum" => ["celsius", "fahrenheit"]
                        ]
                    ],
                    "required" => ["location"]
                ],
            ],
        ];
        $data = [];
        $result = $service->chatWithFunctions($chat, $data, $functions);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('function_call', $result);
        $result['function_call']['arguments'] = json_decode($result['function_call']['arguments'], true);
        $this->assertEquals([
            "role" => "assistant",
            "content" => null,
            "function_call" => [
                "name" => "get_current_weather",
                "arguments" => ["location" => "La Paz"]
            ]
        ], $result);
    }

    public function testChatCallFunction()
    {
        $service = new AIService(env('OPENAI_API_KEY'));
        $data = [
            'location' => 'La Paz',
        ];
        $chat = [
            [
                'role' => 'user',
                'content' => "¿Cómo está el clima en {{{ location }}}?"
            ],
        ];
        $functions = [
            [
                "name" => "get_current_weather",
                "description" => "Obtiene el clima actual en una ubicación determinada",
                "parameters" => (object) [
                    "type" => "object",
                    "properties" => [
                        "location" => [
                            "type" => "string",
                            "description" => "La ciudad y el estado, ej. San Francisco, Cochabamba"
                        ],
                        "unit" => [
                            "type" => "string",
                            "enum" => ["celsius", "fahrenheit"]
                        ]
                    ],
                    "required" => ["location"]
                ],
            ],
        ];
        $service->registerFunction('get_current_weather', function ($arguments): string {
            return "El clima en {$arguments['location']} está buenísimo!";
        });
        $result = $service->chatWithFunctions($chat, $data, $functions);
        $this->assertStringContainsString($data['location'], $result['content']);
        $this->assertStringContainsString('buenísimo', $result['content']);
    }

    public function testPrepareObjectDescription()
    {
        $object = new AnalistaBD();
        $result = $object->aiCode("escribe un query para obtener la cantidad de proyectos por usuario. Primero revisa que tablas utilizar y las columnas respectivas. Luego prepara el query.", "sql");
        $this->assertStringContainsString("SELECT", $result);
        $this->assertStringContainsString("COUNT(", $result);
    }

    public function testAIWithPlanning()
    {
        $analista = new AnalistaBD();
        $analista("escribe un query para obtener la cantidad de proyectos por usuario");
        $query = $analista->query_final;
        $this->assertStringContainsString("SELECT", $query);
        $this->assertStringContainsString("COUNT(", $query);
    }
}

class AnalistaBD
{
    use MakeIntelligentTrait;

    public string $query_final;

    /**
     * Obtiene las tablas de la base de datos
     */
    public function get_database_tables()
    {
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        return $tables;
    }

    /**
     * Obtiene las columnas de una tabla
     *
     * @param string $table_name Nombre de la tabla, ej. usuarios
     */
    public function get_table_columns(string $table_name)
    {
        // Obtener las columnas
        $columns = Schema::getColumnListing($table_name);

        // Obtener el tipo de dato de cada columna
        $columnTypes = [];
        foreach ($columns as $column) {
            $type = DB::getSchemaBuilder()->getColumnType($table_name, $column);
            $columnTypes[$column] = $type;
        }
        return $columnTypes;
    }

    /**
     * Prueba una consulta SQL para verificar que no hay errores
     * 
     * @param string $query Consulta SQL
     */
    public function test_query(string $query)
    {
        try {
            $result = DB::select($query);
            $this->query_final = $query;
            return "Success: " . ($result[0] ? json_encode($result[0]) : "");
        } catch (Exception $error) {
            return $error->getMessage();
        }

        return $result;
    }
}
