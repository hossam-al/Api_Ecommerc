<?php

namespace App\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255|unique:brands,name_en',
            'name_ar' => 'required|string|max:255|unique:brands,name_ar',
            'logo' => 'nullable|string|max:2048',
            'is_active' => 'boolean',
        ];
    }
}
