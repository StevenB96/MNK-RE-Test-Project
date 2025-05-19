<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            VoyagerDatabaseSeeder::class,
            VoyagerDummyDatabaseSeeder::class,
            CurrencySeeder::class,
            TransactionTypeSeeder::class,
            AccountSeeder::class,
            TransactionSeeder::class,
            TrimDefaultMenuItems::class,
            // CreateAccountsDataTypeAndPermissions::class,
            AddMenuItemsAndBreadPermissionsSeeder::class,
            DataTypesAndRowsSeeder::class,
        ]);
    }
}
// \App\Models\User::factory(10)->create();