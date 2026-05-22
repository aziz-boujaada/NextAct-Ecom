<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $table = 'company_settings';

    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'company_website',
        'company_tax_number',
        'company_logo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the logo URL attribute
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->company_logo) {
            return null;
        }

        return asset('storage/' . $this->company_logo);
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        return self::firstOrCreate(
            [],
            [
                'company_name' => 'Your Company',
                'company_email' => 'info@yourcompany.com',
                'company_phone' => '+1 (555) 000-0000',
                'company_address' => '123 Business Street',
            ]
        );
    }
}
