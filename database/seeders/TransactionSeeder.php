<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\Currency;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $creditType = TransactionType::where('name', 'Credit')->first();
        $debitType  = TransactionType::where('name', 'Debit')->first();
        $usd        = Currency::where('code', 'USD')->first();

        Account::all()->each(function ($account) use ($creditType, $debitType, $usd) {
            // Initial deposit to bring balance up to 10,000
            Transaction::create([
                'account_id'            => $account->id,
                'transaction_type_id'   => $creditType->id,
                'currency_id'           => $usd->id,
                'amount'                => 10000,
                'description'           => 'Initial deposit',
            ]);

            // (Optional) Test withdrawal
            Transaction::create([
                'account_id'            => $account->id,
                'transaction_type_id'   => $debitType->id,
                'currency_id'           => $usd->id,
                'amount'                => 100,
                'description'           => 'Test withdrawal',
            ]);
        });
    }
}
