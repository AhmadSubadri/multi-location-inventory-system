<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnDocument extends Model
{
    use SoftDeletes;

    protected $table = 'returns';

    protected $fillable = [
        'code',
        'type',
        'location_id',
        'related_location_id',
        'supplier_id',
        'status',
        'reason',
        'notes',
        'created_by',
        'approved_by',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function relatedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'related_location_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    public static function getNextStatuses(string $currentStatus): array
    {
        return match ($currentStatus) {
            'draft' => ['approved'],
            'approved' => ['completed', 'cancelled'],
            default => [],
        };
    }
}
