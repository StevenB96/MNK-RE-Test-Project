<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\MenuItem;

class TrimDefaultMenuItems extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // List of menu item titles to keep
        $titlesToKeep = [
            'Users',
            'Roles',
        ];

        // Backup items to be deleted
        $itemsToDelete = MenuItem::whereNotIn('title', $titlesToKeep)->get();

        // Store backup data for potential restoration
        $backupData = $itemsToDelete->toArray();

        // Delete items not in the list
        MenuItem::whereNotIn('title', $titlesToKeep)->delete();

        // Save backup data to cache or storage for restoration if needed
        cache()->put('deleted_menu_items_backup', $backupData);
    }

    /**
     * Optional: Restore the deleted menu items
     * Call this method manually if needed.
     */
    public function restoreDeletedItems()
    {
        $backupData = cache()->get('deleted_menu_items_backup', []);

        foreach ($backupData as $itemData) {
            // Remove 'id' to avoid conflicts if auto-increment is used
            unset($itemData['id']);

            // Insert the item back into the database
            MenuItem::create($itemData);
        }
    }
}