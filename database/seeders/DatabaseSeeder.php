<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Edward J. Pineda',
            'email' => 'epineda@yopmail.com',
            'password' => Hash::make('12345678')
        ]);
        User::create([
            'name' => 'Juan Carlos Bodoque',
            'email' => 'jbodoque@yopmail.com',
            'password' => Hash::make('12345678')
        ]);
        User::create([
            'name' => 'Ariadna Aguilar',
            'email' => 'aguilar@yopmail.com',
            'password' => Hash::make('12345678')
        ]);

        Category::create(['name' => 'Alimentacion', 'type' => 'gasto']);
        Category::create(['name' => 'Transporte', 'type' => 'gasto']);
        Category::create(['name' => 'Salud', 'type' => 'gasto']);
        Category::create(['name' => 'Entretenimiento', 'type' => 'gasto']);
        Category::create(['name' => 'Sueldos', 'type' => 'ingreso']);
        Category::create(['name' => 'Inversiones', 'type' => 'ingreso']);
        Category::create(['name' => 'Otros', 'type' => 'gasto']);
        Category::create(['name' => 'Ahorros', 'type' => 'gasto']);
        Category::create(['name' => 'Otros Ingresos', 'type' => 'ingreso']);
        Category::create(['name' => 'Otros Gastos', 'type' => 'gasto']);
    }
}
