<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'account_number', 'currency_id', 'balance'];

    public static function booted()
    {
        static::creating(fn($account) => $account->account_number = self::generateNumber());
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'AC' . now()->format('Ymd') . mt_rand(100000, 999999);
        } while (self::where('account_number', $number)->exists());

        return $number;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
