<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'base_unit_id',
        'description',
        'brand',
        'active_ingredient',
        'registration_number',
        'is_hazardous',
        'hazardous_notes',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_hazardous' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function stockSettings(): HasMany
    {
        return $this->hasMany(ProductStockSetting::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')->withTimestamps();
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total stock at a specific location.
     */
    public function getStockAtLocation(int $locationId): float
    {
        return $this->batches()
            ->where('location_id', $locationId)
            ->sum('remaining_qty');
    }

    /**
     * Get the appropriate price based on quantity and location.
     */
    public function getSellingPrice(int $qty, ?int $locationId = null): ?ProductPrice
    {
        $query = $this->prices()
            ->whereIn('price_type', ['retail', 'wholesale'])
            ->where('min_qty', '<=', $qty)
            ->orderBy('min_qty', 'desc');

        if ($locationId) {
            // Try location-specific price first
            $locationPrice = (clone $query)->where('location_id', $locationId)->first();
            if ($locationPrice) {
                return $locationPrice;
            }
        }

        // Fall back to global price
        return $query->whereNull('location_id')->first();
    }

    /**
     * Get image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return asset('storage/' . $this->image_path);
    }
}
