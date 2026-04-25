<?php

namespace App\Http\Requests\AdminProducts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('admin_product') ?? $this->route('id');
        $productId = is_object($product) ? $product->id : $product;
        $mega = 2 * 1024;

        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|min:3|max:255',
            'price' => 'sometimes|numeric|min:0',
            'sku' => 'nullable|string|unique:admin_products,sku,' . $productId,
            'stock' => 'sometimes|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'image_url' => "nullable|file|mimes:jpg,jpeg,png,webp|max:$mega",
        ];
    }
}
