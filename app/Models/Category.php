<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'type',
    ];

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }
}
