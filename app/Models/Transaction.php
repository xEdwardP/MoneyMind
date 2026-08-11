<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'amount',
        'description',
        'photo',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
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
        // Crear, editar o borrar un movimiento cambia lo gastado en un presupuesto,
        // por lo que los tres casos tienen que refrescarlo.
        static::saved(fn (Transaction $transaction) => $transaction->syncBudgets());
        static::deleted(fn (Transaction $transaction) => $transaction->syncBudgets());
    }

    /**
     * Recalcula los presupuestos afectados por este movimiento. Se toman en cuenta
     * tanto el usuario/categoría actuales como los anteriores, porque al editar un
     * movimiento se debe descontar del presupuesto del que salió.
     */
    public function syncBudgets(): void
    {
        $userIds = array_unique(array_filter([$this->user_id, $this->getOriginal('user_id')]));
        $categoryIds = array_unique(array_filter([$this->category_id, $this->getOriginal('category_id')]));

        if ($userIds === [] || $categoryIds === []) {
            return;
        }

        Budget::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('category_id', $categoryIds)
            ->get()
            ->each
            ->recalculateSpentAmount();
    }
}
