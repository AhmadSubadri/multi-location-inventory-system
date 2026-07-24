<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'batch_id',
        'qty_sent',
        'qty_received',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qty_sent' => 'decimal:2',
            'qty_received' => 'decimal:2',
        ];
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    /**
     * Get quantity difference (sent vs received).
     */
    public function getDifferenceAttribute(): ?float
    {
        if ($this->qty_received === null) {
            return null;
        }

        return $this->qty_sent - $this->qty_received;
    }
}
