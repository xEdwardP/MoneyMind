<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $table = 'budgets';

    protected $fillable = [
        'user_id',
        'category_id',
        'assignedAmount',
        'spentAmount',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'assignedAmount' => 'decimal:2',
            'spentAmount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted(): void
    {
        // El periodo o la categoría pueden cambiar al editar, así que el monto
        // gastado se vuelve a calcular en lugar de arrastrar el valor anterior.
        static::saved(fn (Budget $budget) => $budget->recalculateSpentAmount());
    }

    /**
     * Recalcula el monto gastado sumando los movimientos de gasto del mismo
     * usuario y categoría que caen dentro del periodo del presupuesto.
     */
    public function recalculateSpentAmount(): void
    {
        $spent = Transaction::query()
            ->where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('type', 'gasto')
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->sum('amount');

        if ((float) $this->spentAmount === (float) $spent) {
            return;
        }

        // saveQuietly evita volver a disparar el evento "saved" de este modelo.
        $this->forceFill(['spentAmount' => $spent])->saveQuietly();
    }

    /**
     * Porcentaje del presupuesto consumido, acotado a 100 para la barra de progreso.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ((float) $this->assignedAmount <= 0) {
            return 0;
        }

        return min(100, round(((float) $this->spentAmount / (float) $this->assignedAmount) * 100, 1));
    }
}
