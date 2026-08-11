<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'password' => 'password',
    ]);

    $this->category = Category::create(['name' => 'Alimentacion', 'type' => 'gasto']);

    $this->budget = Budget::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'assignedAmount' => 500,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
    ]);
});

function expense(array $attributes = []): Transaction
{
    return Transaction::create(array_merge([
        'user_id' => test()->user->id,
        'category_id' => test()->category->id,
        'type' => 'gasto',
        'amount' => 100,
        'transaction_date' => now(),
    ], $attributes));
}

it('adds up expenses that fall inside the budget period', function () {
    expense(['amount' => 120.50]);
    expense(['amount' => 30.25]);

    expect((float) $this->budget->fresh()->spentAmount)->toBe(150.75);
});

it('ignores expenses outside the budget period', function () {
    expense(['transaction_date' => now()->subMonths(2)]);

    expect((float) $this->budget->fresh()->spentAmount)->toBe(0.0);
});

it('ignores income transactions', function () {
    $income = Category::create(['name' => 'Sueldos', 'type' => 'ingreso']);

    expense(['type' => 'ingreso', 'category_id' => $income->id, 'amount' => 900]);

    expect((float) $this->budget->fresh()->spentAmount)->toBe(0.0);
});

it('updates the spent amount when a transaction is edited', function () {
    $transaction = expense(['amount' => 200]);

    expect((float) $this->budget->fresh()->spentAmount)->toBe(200.0);

    $transaction->update(['amount' => 45]);

    expect((float) $this->budget->fresh()->spentAmount)->toBe(45.0);
});

it('updates the spent amount when a transaction leaves the period', function () {
    $transaction = expense(['amount' => 200]);

    $transaction->update(['transaction_date' => now()->subMonths(3)]);

    expect((float) $this->budget->fresh()->spentAmount)->toBe(0.0);
});

it('updates the spent amount when a transaction is deleted', function () {
    $transaction = expense(['amount' => 75]);

    $transaction->delete();

    expect((float) $this->budget->fresh()->spentAmount)->toBe(0.0);
});

it('discounts the old category when a transaction is moved', function () {
    $other = Category::create(['name' => 'Transporte', 'type' => 'gasto']);
    $transaction = expense(['amount' => 60]);

    expect((float) $this->budget->fresh()->spentAmount)->toBe(60.0);

    $transaction->update(['category_id' => $other->id]);

    expect((float) $this->budget->fresh()->spentAmount)->toBe(0.0);
});

it('caps the usage percentage at 100', function () {
    expense(['amount' => 5000]);

    expect($this->budget->fresh()->usage_percentage)->toBe(100.0);
});
