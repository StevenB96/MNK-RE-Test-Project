<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionType;

class TransactionTypeSeeder extends Seeder
{
    public function run()
    {
        foreach (['Credit', 'Debit'] as $type) {
            TransactionType::updateOrCreate(['name' => $type]);
        }
    }
}
