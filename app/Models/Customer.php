<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'is_demo',
        'dni',
        'age',
        'city',
        'health_interest',
        'last_purchase_date',
        'visit_count',
        'segment',
        'treatment_status',
        'treatment_start_date',
        'treatment_end_date',
        'health_notes',
    ];

    protected function casts(): array
    {
        return [
            'last_purchase_date' => 'date',
            'is_demo' => 'boolean',
            'treatment_start_date' => 'date',
            'treatment_end_date' => 'date',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PatientOrder::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
