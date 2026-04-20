<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasFile('images') && !is_array($this->file('images'))) {
            $this->files->set('images', [$this->file('images')]);
        }
    }

    public function rules(): array
    {
        $mega = 2 * 1024;

        return [
            'name_en' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'variants' => 'sometimes|array|min:1',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.color' => 'required_with:variants|string',
            'variants.*.size' => 'required_with:variants|string',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
            'variants.*.sku' => 'nullable|string|distinct',
            'variants.*.primary_image' => "nullable|file|mimes:jpg,jpeg,png,webp|max:$mega",
            'variants.*.gallery_images' => 'nullable|array',
            'variants.*.gallery_images.*' => "file|mimes:jpg,jpeg,png,webp|max:$mega",
            'image_url' => "nullable|file|mimes:jpg,jpeg,png,webp|max:$mega",
            'images' => 'nullable|array',
            'images.*' => "file|mimes:jpg,jpeg,png,webp|max:$mega",
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'review_status' => 'sometimes|string|in:pending,approved,rejected',
            'discount_type' => 'sometimes|nullable|string|in:percentage,fixed',
            'discount_value' => 'sometimes|nullable|numeric|min:0',
            'discount_start_at' => 'sometimes|nullable|date',
            'discount_end_at' => 'sometimes|nullable|date',
        ];
    }
}
