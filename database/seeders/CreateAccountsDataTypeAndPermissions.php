<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class CreateAccountsDataTypeAndPermissions extends Seeder
{
    public function run()
    {
        // Step 1: Create or update DataType
        DataType::updateOrCreate(
            ['slug' => 'accounts'],
            [
                'name'                  => 'accounts',
                'display_name_singular' => 'Account',
                'display_name_plural'   => 'Accounts',
                'icon'                  => null,
                'model_name'            => 'App\\Models\\Account',
                'policy_name'           => null,
                'controller'            => null,
                'description'           => null,
                'generate_permissions'  => 1,
                'server_side'           => 0,
                'details'               => null,
            ]
        );

        // Step 2: Generate permissions
        $actions = ['browse', 'read', 'edit', 'add', 'delete'];
        $dataTypeSlug = 'accounts';

        $permissions = [];
        foreach ($actions as $action) {
            $permissionSlug = "{$action}_{$dataTypeSlug}";
            $permissions[] = Permission::firstOrCreate(['key' => $permissionSlug]);
        }

        // Step 3: Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            foreach ($permissions as $permission) {
                if (!$adminRole->permissions()->where('permission_id', $permission->id)->exists()) {
                    $adminRole->permissions()->attach($permission->id);
                }
            }
        }
    }
}