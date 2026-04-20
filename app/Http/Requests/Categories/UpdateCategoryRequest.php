<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('category');

        return [
            'name_en' => 'sometimes|string|unique:categories,name_en,' . $id,
            'name_ar' => 'sometimes|string|unique:categories,name_ar,' . $id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'image_url' => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'is_active' => 'boolean',
        ];
    }
}
