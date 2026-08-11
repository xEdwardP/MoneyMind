<?php

use App\Filament\Resources\BudgetResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\TransactionResource;
use App\Filament\Widgets\ExpensesChart;
use App\Filament\Widgets\IncomeChart;
use App\Filament\Widgets\StatsOverview;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::create([
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'password' => 'password',
    ]));
});

it('redirects guests from the panel to the login page', function () {
    auth()->logout();

    $this->get('/admin')->assertRedirect('/admin/login');
});

it('renders the dashboard', function () {
    $this->get('/admin')->assertSuccessful();
});

it('renders the stats overview widget', function () {
    $category = Category::create(['name' => 'Sueldos', 'type' => 'ingreso']);

    Transaction::create([
        'user_id' => auth()->id(),
        'category_id' => $category->id,
        'type' => 'ingreso',
        'amount' => 1500,
        'transaction_date' => now(),
    ]);

    Livewire::test(StatsOverview::class)
        ->assertSuccessful()
        ->assertSee(Number::format(1500, precision: 2, locale: app()->getLocale()));
});

// Los datasets van anidados a propósito: Pest interpreta un array plano de dos
// strings como un callable [clase, método].
it('renders both monthly charts', function (string $widget) {
    Livewire::test($widget)->assertSuccessful();
})->with([[IncomeChart::class], [ExpensesChart::class]]);

it('renders every resource list page', function (string $resource) {
    $this->get($resource::getUrl('index'))->assertSuccessful();
})->with([CategoryResource::class, TransactionResource::class, BudgetResource::class]);

it('renders every resource create page', function (string $resource) {
    $this->get($resource::getUrl('create'))->assertSuccessful();
})->with([CategoryResource::class, TransactionResource::class, BudgetResource::class]);

it('renders the edit page of an existing record', function () {
    $category = Category::create(['name' => 'Alimentacion', 'type' => 'gasto']);

    $transaction = Transaction::create([
        'user_id' => auth()->id(),
        'category_id' => $category->id,
        'type' => 'gasto',
        'amount' => 45.5,
        'description' => '<p>Supermercado</p>',
        'transaction_date' => now(),
    ]);

    $budget = Budget::create([
        'user_id' => auth()->id(),
        'category_id' => $category->id,
        'assignedAmount' => 300,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
    ]);

    $this->get(CategoryResource::getUrl('edit', ['record' => $category]))->assertSuccessful();
    $this->get(TransactionResource::getUrl('edit', ['record' => $transaction]))->assertSuccessful();
    $this->get(BudgetResource::getUrl('edit', ['record' => $budget]))->assertSuccessful();
});
