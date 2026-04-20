<?php

namespace App\Http\Requests\AdminProducts;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'sku' => 'nullable|string|unique:admin_products,sku',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }
}
