<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationMetadataService
{
    public function getColumnsFromTable($tableName)
    {
        $columns = Schema::getColumnListing($tableName);
        $columnDetails = DB::select("SHOW COLUMNS FROM {$tableName}");
        $columnData = [];

        foreach ($columnDetails as $columnDetail) {
            $type = $this->mapColumnType($columnDetail->Type);
            if (! str_contains($columnDetail->Field, '_id') && $columnDetail->Field !== 'status' && $type !== 'timestamp' && $columnDetail->Field !== 'created_at' && $columnDetail->Field !== 'id') {
                $columnData[] = [
                    'type' => $type,
                    'name' => $columnDetail->Field,
                    'libelle' => str_replace('_', ' ', $columnDetail->Field),
                ];
            } elseif (str_contains($columnDetail->Field, '_id')) {
                $foreignTableName = $this->getForeignTableName($tableName, $columnDetail->Field);
                $columnData[] = [
                    'type' => 'foreign',
                    'name' => $columnDetail->Field,
                    'libelle' => str_replace('_', ' ', $foreignTableName),
                    'references' => 'id', // Assuming foreign key references 'id' column
                    'on' => $foreignTableName,
                ];
            }
        }

        // Trier les colonnes pour mettre les clés étrangères en premier
        usort($columnData, function ($a, $b) {
            if ($a['type'] === 'foreign' && $b['type'] !== 'foreign') {
                return -1;
            } elseif ($a['type'] !== 'foreign' && $b['type'] === 'foreign') {
                return 1;
            }

            return 0;
        });

        return $columnData;
    }

    private function getForeignTableName($tableName, $foreignKey)
    {
        $query = '
            SELECT
                kcu.referenced_table_name AS foreign_table
            FROM
                information_schema.key_column_usage kcu
            WHERE
                kcu.table_schema = ? AND
                kcu.table_name = ? AND
                kcu.column_name = ? AND
                kcu.referenced_table_name IS NOT NULL
            LIMIT 1
        ';

        $result = DB::select($query, [env('DB_DATABASE'), $tableName, $foreignKey]);

        return $result[0]->foreign_table ?? str_replace('_id', '', $foreignKey);
    }

    private function mapColumnType($dbType)
    {
        if (str_contains($dbType, 'int')) {
            return 'integer';
        } elseif (str_contains($dbType, 'varchar') || str_contains($dbType, 'text')) {
            return 'string';
        } elseif (str_contains($dbType, 'date') || str_contains($dbType, 'datetime')) {
            return 'date';
        } elseif (str_contains($dbType, 'timestamp')) {
            return 'timestamp';
        }

        // Add more mappings as needed
        return 'string';
    }

    public function getMigrationMetadata($tableName)
    {
        return $this->getColumnsFromTable($tableName);
    }

    public function getRelatedData($columns)
    {
        $relatedData = [];
        foreach ($columns as $column) {
            if ($column['type'] === 'foreign') {
                $relatedTable = $column['on'];
                $relatedData[$column['name']] = DB::table($relatedTable)->where('status', 'activer')->get();
            }
        }

        return $relatedData;
    }
}
