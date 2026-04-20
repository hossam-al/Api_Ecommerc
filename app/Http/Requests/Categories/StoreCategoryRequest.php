<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|unique:categories,name_en',
            'name_ar' => 'required|string|unique:categories,name_ar',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'image_url' => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'is_active' => 'boolean',
        ];
    }
}
