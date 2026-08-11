<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect([
            ['name' => 'Edward J. Pineda', 'email' => 'epineda@yopmail.com'],
            ['name' => 'Juan Carlos Bodoque', 'email' => 'jbodoque@yopmail.com'],
            ['name' => 'Ariadna Aguilar', 'email' => 'aguilar@yopmail.com'],
        ])->map(fn (array $user) => User::firstOrCreate(
            ['email' => $user['email']],
            ['name' => $user['name'], 'password' => Hash::make('12345678')],
        ));

        $categories = collect([
            ['name' => 'Alimentacion', 'type' => 'gasto'],
            ['name' => 'Transporte', 'type' => 'gasto'],
            ['name' => 'Salud', 'type' => 'gasto'],
            ['name' => 'Entretenimiento', 'type' => 'gasto'],
            ['name' => 'Sueldos', 'type' => 'ingreso'],
            ['name' => 'Inversiones', 'type' => 'ingreso'],
            ['name' => 'Otros', 'type' => 'gasto'],
            ['name' => 'Ahorros', 'type' => 'gasto'],
            ['name' => 'Otros Ingresos', 'type' => 'ingreso'],
            ['name' => 'Otros Gastos', 'type' => 'gasto'],
        ])->map(fn (array $category) => Category::firstOrCreate(
            ['name' => $category['name']],
            ['type' => $category['type']],
        ));

        if (Transaction::exists()) {
            return;
        }

        $this->seedSampleMovements($users, $categories);
    }

    /**
     * Genera un año de movimientos y un presupuesto mensual por usuario para que
     * el dashboard y las gráficas tengan datos con los que trabajar.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Category>  $categories
     */
    protected function seedSampleMovements(Collection $users, Collection $categories): void
    {
        $incomeCategories = $categories->where('type', 'ingreso');
        $expenseCategories = $categories->where('type', 'gasto');

        foreach ($users as $user) {
            for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
                $month = Carbon::now()->subMonths($monthsAgo)->startOfMonth();

                Transaction::create([
                    'user_id' => $user->id,
                    'category_id' => $incomeCategories->firstWhere('name', 'Sueldos')->id,
                    'type' => 'ingreso',
                    'amount' => random_int(180000, 260000) / 100,
                    'description' => '<p>Salario del mes</p>',
                    'transaction_date' => $month->copy()->addDays(4),
                ]);

                foreach ($expenseCategories->random(3) as $index => $category) {
                    Transaction::create([
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                        'type' => 'gasto',
                        'amount' => random_int(1500, 45000) / 100,
                        'description' => "<p>Gasto en {$category->name}</p>",
                        'transaction_date' => $month->copy()->addDays(7 + ($index * 6)),
                    ]);
                }
            }

            Budget::create([
                'user_id' => $user->id,
                'category_id' => $expenseCategories->firstWhere('name', 'Alimentacion')->id,
                'assignedAmount' => 800.00,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->endOfMonth(),
            ]);
        }
    }
}
