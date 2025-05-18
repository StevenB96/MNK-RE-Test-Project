<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\User;
use App\Models\Currency;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::where('code', 'USD')->first();

        User::all()->each(function ($user) use ($currency) {
            Account::create([
                'user_id' => $user->id,
                'currency_id' => $currency->id,
                'balance' => 10000,
            ]);
        });
    }
}
