<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'from_location_id',
        'to_location_id',
        'status',
        'notes',
        'requested_by',
        'approved_by',
        'shipped_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInTransit(): bool
    {
        return $this->status === 'shipped';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Valid status transitions.
     */
    public static function getNextStatuses(string $currentStatus): array
    {
        return match ($currentStatus) {
            'draft' => ['submitted'],
            'submitted' => ['approved', 'cancelled'],
            'approved' => ['shipped', 'cancelled'],
            'shipped' => ['received'],
            default => [],
        };
    }
}
