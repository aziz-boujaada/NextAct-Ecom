<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanySettingsRequest;
use App\Services\CompanySettingsService;

class CompanySettingsController extends Controller
{
    public function __construct(private readonly CompanySettingsService $companySettingsService) {}

    /**
     * Get current company settings
     */
    public function show()
    {
        $settings = $this->companySettingsService->getSettings();

        return response()->json([
            'status' => 'success',
            'data' => $this->companySettingsService->transformForResponse($settings),
        ]);
    }

    /**
     * Update company settings
     */
    public function update(CompanySettingsRequest $request)
    {
        $validated = $request->validated();

        // Service handles logo upload and all updates
        $settings = $this->companySettingsService->updateSettings($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Company settings updated successfully',
            'data' => $this->companySettingsService->transformForResponse($settings),
        ]);
    }

    /**
     * Delete company logo
     */
    public function deleteLogo()
    {
        $settings = $this->companySettingsService->deleteLogo();

        return response()->json([
            'status' => 'success',
            'message' => 'Company logo deleted successfully',
            'data' => $this->companySettingsService->transformForResponse($settings),
        ]);
    }
}
