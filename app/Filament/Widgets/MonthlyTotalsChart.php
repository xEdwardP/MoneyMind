<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

/**
 * Base para las gráficas mensuales de ingresos y gastos.
 *
 * La agrupación por mes se hace en PHP a propósito: `MONTH()` sólo existe en
 * MySQL y rompía la suite de tests, que corre sobre SQLite.
 */
abstract class MonthlyTotalsChart extends ChartWidget
{
    protected const MONTHS = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ];

    /**
     * Tipo de movimiento que resume la gráfica: "ingreso" o "gasto".
     */
    abstract protected function getTransactionType(): string;

    abstract protected function getDatasetLabel(): string;

    abstract protected function getDatasetColor(): string;

    protected function getData(): array
    {
        $color = $this->getDatasetColor();

        return [
            'datasets' => [
                [
                    'label' => $this->getDatasetLabel(),
                    'data' => $this->getMonthlyTotals(),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'fill' => false,
                ],
            ],
            'labels' => self::MONTHS,
        ];
    }

    /**
     * @return list<float> Un total por mes del año en curso, de enero a diciembre.
     */
    protected function getMonthlyTotals(): array
    {
        /** @var Collection<int, float> $totals */
        $totals = Transaction::query()
            ->where('type', $this->getTransactionType())
            ->whereYear('transaction_date', now()->year)
            ->get(['transaction_date', 'amount'])
            ->groupBy(fn (Transaction $transaction) => $transaction->transaction_date->month)
            ->map(fn (Collection $transactions) => (float) $transactions->sum('amount'));

        return collect(range(1, 12))
            ->map(fn (int $month) => $totals->get($month, 0.0))
            ->all();
    }

    protected function getType(): string
    {
        return 'line';
    }
}
