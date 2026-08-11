<?php

namespace App\Filament\Widgets;

class ExpensesChart extends MonthlyTotalsChart
{
    protected static ?string $heading = 'Reporte de movimiento de gastos';

    protected static ?int $sort = 3;

    protected function getTransactionType(): string
    {
        return 'gasto';
    }

    protected function getDatasetLabel(): string
    {
        return 'Gastos';
    }

    protected function getDatasetColor(): string
    {
        return '#FF5733';
    }
}
