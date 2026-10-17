<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
        'name',
        'stock_qty',
        'unit',
        'min_stock_alert',
        'cost_per_unit',
    ];

    protected function casts(): array
    {
        return [
            'stock_qty' => 'decimal:2',
            'min_stock_alert' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
        ];
    }
}
