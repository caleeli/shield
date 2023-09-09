<?php

namespace App\Agents;

use App\Services\MakeIntelligentTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalistaBD
{
    use MakeIntelligentTrait;

    public string $query;

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
            $this->query = $query;
            return "Success: " . ($result[0] ? json_encode($result[0]) : "");
        } catch (Exception $error) {
            return $error->getMessage();
        }

        return $result;
    }
}
