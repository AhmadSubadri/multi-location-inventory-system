<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'location_id',
        'customer_name',
        'customer_contact',
        'record_type',
        'sold_at',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'reference_number',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesRecordItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
