<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionType;
use App\Models\Currency;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $credit = TransactionType::where('name', 'Credit')->first();
        $debit = TransactionType::where('name', 'Debit')->first();
        $currency = Currency::where('code', 'USD')->first();

        Account::all()->each(function ($account) use ($credit, $debit, $currency) {
            Transaction::create([
                'account_id' => $account->id,
                'transaction_type_id' => $credit->id,
                'amount' => 500,
                'currency_id' => $currency->id,
                'description' => 'Initial deposit',
            ]);

            Transaction::create([
                'account_id' => $account->id,
                'transaction_type_id' => $debit->id,
                'amount' => 100,
                'currency_id' => $currency->id,
                'description' => 'Test withdrawal',
            ]);
        });
    }
}
