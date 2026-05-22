<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanySettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:50',
            'company_address' => 'required|string|max:1000',
            'company_website' => 'nullable|url|max:255',
            'company_tax_number' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Company name is required',
            'company_email.required' => 'Company email is required',
            'company_email.email' => 'Company email must be a valid email address',
            'company_phone.required' => 'Company phone is required',
            'company_address.required' => 'Company address is required',
            'company_website.url' => 'Company website must be a valid URL',
            'company_logo.image' => 'Company logo must be an image',
            'company_logo.mimes' => 'Company logo must be a JPEG, PNG, GIF, or WebP image',
            'company_logo.max' => 'Company logo must not exceed 2MB',
        ];
    }
}
