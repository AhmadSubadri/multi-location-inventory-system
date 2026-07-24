<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockLedger extends Model
{
    protected $fillable = [
        'product_id',
        'batch_id',
        'location_id',
        'type',
        'qty',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public $timestamps = true;
    const UPDATED_AT = null; // Ledger entries are immutable

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference document (polymorphic).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }

    /**
     * Human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'IN' => 'Penerimaan Barang',
            'OUT_SALE' => 'Penjualan',
            'TRANSFER_OUT' => 'Transfer Keluar',
            'TRANSFER_IN' => 'Transfer Masuk',
            'RETURN_IN' => 'Retur Masuk',
            'RETURN_OUT' => 'Retur Keluar',
            'ADJUSTMENT' => 'Penyesuaian',
            default => $this->type,
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this->type, ['IN', 'TRANSFER_IN', 'RETURN_IN']) || 
               ($this->type === 'ADJUSTMENT' && $this->qty > 0);
    }
}
