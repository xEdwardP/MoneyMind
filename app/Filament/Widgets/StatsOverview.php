<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $income = (float) Transaction::where('type', 'ingreso')->sum('amount');
        $expenses = (float) Transaction::where('type', 'gasto')->sum('amount');
        $balance = $income - $expenses;

        return [
            Stat::make('Ingresos', $this->formatMoney($income))
                ->description('Total de ingresos registrados')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Gastos', $this->formatMoney($expenses))
                ->description('Total de gastos registrados')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make('Balance', $this->formatMoney($balance))
                ->description($balance >= 0 ? 'Saldo a favor' : 'Saldo en contra')
                ->icon('heroicon-o-scale')
                ->color($balance >= 0 ? 'success' : 'danger'),

            Stat::make('Usuarios', User::count())
                ->description('Total de usuarios registrados')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Categorías', Category::count())
                ->description('Total de categorías registradas')
                ->icon('heroicon-o-briefcase')
                ->color('primary'),

            Stat::make('Movimientos', Transaction::count())
                ->description('Total de movimientos registrados')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary'),
        ];
    }

    /**
     * Usa el locale de la app para que coincida con el formato de las tablas.
     */
    protected function formatMoney(float $amount): string
    {
        return '$ '.Number::format($amount, precision: 2, locale: app()->getLocale());
    }
}
