<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'amount',
        'description',
        'photo',
        'transaction_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted()
    {
        static::created(function ($transaction) {
            if ($transaction->type == 'gasto') {
                $budget = Budget::where('user_id', $transaction->user_id)
                    ->where('category_id', $transaction->category_id)
                    ->whereMonth('start_date', now()->month)
                    ->whereYear('start_date', now()->year)
                    ->first();

                if ($budget) {
                    // $budget->spentAmount += $transaction->amount;
                    // $budget->save();
                    $budget->increment('spentAmount', $transaction->amount);
                }
            }
        });
    }
}
