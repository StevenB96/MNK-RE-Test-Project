<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataTypesAndRowsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Load JSON export (place data_types.json in database/seeders)
        $json = file_get_contents(database_path('seeders/data_types.json'));
        $export = json_decode($json, true);
        // The third element is the table export
        $rows = $export[2]['data'];

        $dataTypes = [];
        $dataRows = [];

        foreach ($rows as $item) {
            $dtId = (int) $item['data_type_id'];

            // Prepare data_types entry
            if (!isset($dataTypes[$dtId])) {
                $dataTypes[$dtId] = [
                    'id' => $dtId,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'display_name_singular' => $item['display_name_singular'],
                    'display_name_plural' => $item['display_name_plural'],
                    'icon' => $item['icon'],
                    'model_name' => $item['model_name'],
                    'policy_name' => $item['policy_name'],
                    'controller' => $item['controller'],
                    'description' => $item['description'],
                    'generate_permissions' => (int) $item['generate_permissions'],
                    'server_side' => (int) $item['server_side'],
                    'details' => $item['details'],
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                ];
            }

            // Prepare data_rows entry without id to let auto-increment on insert
            $dataRows[] = [
                'data_type_id'   => $dtId,
                'field'          => $item['field'],
                'type'           => $item['type'],
                'display_name'   => $item['display_name'],
                'required'       => (int) $item['required'],
                'browse'         => (int) $item['browse'],
                'read'           => (int) $item['read'],
                'edit'           => (int) $item['edit'],
                'add'            => (int) $item['add'],
                'delete'         => (int) $item['delete'],
                'details'        => $item['details'],
                'order'          => (int) $item['order'],
            ];
        }

        // Upsert data_types (preserves existing rows and updates if changed)
        DB::table('data_types')->upsert(
            array_values($dataTypes),
            ['id'],
            [
                'name', 'slug', 'display_name_singular', 'display_name_plural',
                'icon', 'model_name', 'policy_name', 'controller',
                'description', 'generate_permissions', 'server_side', 'details',
                'updated_at'
            ]
        );

        // Upsert data_rows based on data_type_id + field, let new rows get new IDs
        DB::table('data_rows')->upsert(
            $dataRows,
            ['data_type_id', 'field'],
            [
                'type', 'display_name',
                'required', 'browse', 'read', 'edit', 'add', 'delete',
                'details', 'order'
            ]
        );
    }
}
