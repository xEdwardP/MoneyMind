<?php

namespace App\Filament\Widgets;

class IncomeChart extends MonthlyTotalsChart
{
    protected static ?string $heading = 'Reporte de movimiento de ingresos';

    protected static ?int $sort = 2;

    protected function getTransactionType(): string
    {
        return 'ingreso';
    }

    protected function getDatasetLabel(): string
    {
        return 'Ingresos';
    }

    protected function getDatasetColor(): string
    {
        return '#4CAF50';
    }
}
