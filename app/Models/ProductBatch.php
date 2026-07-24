<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'batch_number',
        'production_date',
        'expiry_date',
        'initial_qty',
        'remaining_qty',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'expiry_date' => 'date',
            'initial_qty' => 'decimal:2',
            'remaining_qty' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Check if batch is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Check if batch is near expiry (within given days).
     */
    public function isNearExpiry(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= $days && !$this->isExpired();
    }

    /**
     * Scope: FEFO ordering (First Expired First Out).
     */
    public function scopeFefo($query)
    {
        return $query->orderByRaw('ISNULL(expiry_date) ASC, expiry_date ASC');
    }

    public function scopeAvailable($query)
    {
        return $query->where('remaining_qty', '>', 0);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>', now());
        });
    }
}
