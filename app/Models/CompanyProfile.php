<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'name',
        'tagline',
        'logo_path',
        'address',
        'phone',
        'email',
        'npwp',
        'default_tax_percent',
        'currency_symbol',
        'currency_code',
    ];

    protected $appends = ['logo_url'];

    protected function casts(): array
    {
        return [
            'default_tax_percent' => 'decimal:2',
        ];
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return asset('storage/' . $this->logo_path);
    }
}
