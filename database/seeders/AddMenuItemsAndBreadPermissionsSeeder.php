<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class AddMenuItemsAndBreadPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::where('name', 'admin')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $items = [
            ['title' => 'Accounts',           'route' => 'voyager.accounts.index',           'icon' => 'voyager-boat',     'order' => 1, 'table' => 'accounts'],
            ['title' => 'Currencies',         'route' => 'voyager.currencies.index',         'icon' => 'voyager-dollar',   'order' => 2, 'table' => 'currencies'],
            ['title' => 'Transaction Types',  'route' => 'voyager.transaction-types.index',  'icon' => 'voyager-credit-card',     'order' => 3, 'table' => 'transaction_types'],
            ['title' => 'Transactions',       'route' => 'voyager.transactions.index',       'icon' => 'voyager-list', 'order' => 4, 'table' => 'transactions'],
        ];

        foreach ($items as $item) {
            // Create or fetch menu item
            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => $item['title'],
            ]);

            if (! $menuItem->exists) {
                $menuItem->fill([
                    'url'        => '',
                    'route'      => $item['route'],
                    'target'     => '_self',
                    'icon_class' => $item['icon'],
                    'parent_id'  => null,
                    'order'      => $item['order'],
                ])->save();
            }

            // Generate BREAD permissions for the table
            Permission::generateFor($item['table']);

            // Attach all generated permissions to admin role
            $perms = Permission::where('table_name', $item['table'])->get();
            $adminRole->permissions()->syncWithoutDetaching($perms->pluck('id')->toArray());
        }
    }
}