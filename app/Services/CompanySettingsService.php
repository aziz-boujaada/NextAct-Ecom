<?php

namespace App\Services;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Storage;

class CompanySettingsService
{
    /**
     * Get current company settings
     */
    public function getSettings(): CompanySetting
    {
        return CompanySetting::getInstance();
    }

    /**
     * Update company settings
     */
    public function updateSettings(array $data): CompanySetting
    {
        $settings = CompanySetting::getInstance();

        // Handle logo upload
        if (isset($data['company_logo'])) {
            $this->handleLogoUpload($settings, $data['company_logo']);
            unset($data['company_logo']);
        }

        // Update settings
        $settings->update($data);

        return $settings->fresh();
    }

    /**
     * Handle logo upload
     */
    private function handleLogoUpload(CompanySetting $settings, $logoFile): CompanySetting
    {
        // Delete old logo if exists
        if ($settings->company_logo && Storage::disk('public')->exists($settings->company_logo)) {
            Storage::disk('public')->delete($settings->company_logo);
        }

        // Store new logo
        $logoPath = $logoFile->store('logos', 'public');

        $settings->company_logo = $logoPath;
        $settings->save();

        return $settings;
    }

    /**
     * Delete logo
     */
    public function deleteLogo(): CompanySetting
    {
        $settings = CompanySetting::getInstance();

        if ($settings->company_logo && Storage::disk('public')->exists($settings->company_logo)) {
            Storage::disk('public')->delete($settings->company_logo);
        }

        $settings->company_logo = null;
        $settings->save();

        return $settings;
    }

    /**
     * Transform settings for API response
     */
    public function transformForResponse(CompanySetting $settings): array
    {
        return [
            'id' => $settings->id,
            'company_name' => $settings->company_name,
            'company_email' => $settings->company_email,
            'company_phone' => $settings->company_phone,
            'company_address' => $settings->company_address,
            'company_website' => $settings->company_website,
            'company_tax_number' => $settings->company_tax_number,
            'company_logo' => $settings->company_logo,
            'logo_url' => $settings->logo_url,
            'created_at' => $settings->created_at,
            'updated_at' => $settings->updated_at,
        ];
    }
}
