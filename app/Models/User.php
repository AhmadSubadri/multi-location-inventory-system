<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_user')->withTimestamps();
    }

    /**
     * Check if user is Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Check if user is Owner.
     */
    public function isOwner(): bool
    {
        return $this->hasRole('Owner');
    }

    /**
     * Check if user has access to a specific location.
     * Super Admin and Owner have access to all locations.
     */
    public function hasLocationAccess(int $locationId): bool
    {
        if ($this->isSuperAdmin() || $this->isOwner()) {
            return true;
        }

        return $this->locations()->where('locations.id', $locationId)->exists();
    }

    /**
     * Alias for hasLocationAccess.
     */
    public function canAccessLocation(int $locationId): bool
    {
        return $this->hasLocationAccess($locationId);
    }

    /**
     * Get location IDs this user can access.
     */
    public function getAccessibleLocationIds(): array
    {
        if ($this->isSuperAdmin() || $this->isOwner()) {
            return Location::where('is_active', true)->pluck('id')->toArray();
        }

        return $this->locations()->where('is_active', true)->pluck('locations.id')->toArray();
    }
}
