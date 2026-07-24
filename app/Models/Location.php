<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_main_source',
        'address',
        'phone',
        'pic_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_main_source' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'location_user')->withTimestamps();
    }

    public function productBatches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function stockSettings(): HasMany
    {
        return $this->hasMany(ProductStockSetting::class);
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWarehouses($query)
    {
        return $query->where('type', 'warehouse');
    }

    public function scopeStores($query)
    {
        return $query->where('type', 'store');
    }

    public function isWarehouse(): bool
    {
        return $this->type === 'warehouse';
    }

    public function isStore(): bool
    {
        return $this->type === 'store';
    }

    /**
     * Get current stock for a product at this location.
     */
    public function getProductStock(int $productId): float
    {
        return $this->productBatches()
            ->where('product_id', $productId)
            ->sum('remaining_qty');
    }
}
