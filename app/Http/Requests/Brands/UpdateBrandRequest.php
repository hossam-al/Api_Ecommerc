<?php

namespace App\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('brand');

        return [
            'name_en' => 'sometimes|string|max:255|unique:brands,name_en,' . $id,
            'name_ar' => 'sometimes|string|max:255|unique:brands,name_ar,' . $id,
            'logo' => 'nullable|string|max:2048',
            'is_active' => 'boolean',
        ];
    }
}
