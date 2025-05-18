<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionType;

class TransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Credit', 'Debit'];

        foreach ($types as $type) {
            TransactionType::updateOrCreate(['name' => $type]);
        }
    }
}
