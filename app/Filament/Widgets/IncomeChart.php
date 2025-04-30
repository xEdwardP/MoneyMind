<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Reporte de movimiento de ingresos';

    protected function getData(): array
    {
        $data = Transaction::where('type', 'ingreso')
            ->selectRaw('SUM(amount) as total, MONTH(transaction_date) as month')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $totalRevenue = array_fill(0, 12, 0);

        foreach ($data as $item) {
            $totalRevenue[$item->month - 1] = $item->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $totalRevenue,
                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#4CAF50',
                    'fill' => false,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
