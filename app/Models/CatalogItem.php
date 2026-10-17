<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogItem extends Model
{
    protected $fillable = [
        'brand',
        'name',
        'health_category',
        'presentation_type',
        'weight_volume',
        'price_public',
        'price_dealer',
        'stock',
        'lot_number',
        'manufactured_at',
        'expires_at',
        'shelf_location',
        'image',
        'benefits',
        'usage_instructions',
    ];

    protected function casts(): array
    {
        return [
            'price_public' => 'decimal:2',
            'price_dealer' => 'decimal:2',
            'stock' => 'integer',
            'manufactured_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(PatientOrderItem::class);
    }
}
