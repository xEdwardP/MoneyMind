<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Dashboard extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::count())
                ->label('Usuarios')
                ->description('Total de usuarios registrados')
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Categories', Category::count())
                ->label('Categorías')
                ->description('Total de categorías registradas')
                ->icon('heroicon-o-briefcase')
                ->color('primary'),

            Stat::make('Transactions', Transaction::where('type', 'ingreso')->sum('amount') . ' $')
                ->label('Ingresos')
                ->description('Total de ingresos')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart([1,5,3,4,15,6,7,18,9,10]),
        ];
    }
}
