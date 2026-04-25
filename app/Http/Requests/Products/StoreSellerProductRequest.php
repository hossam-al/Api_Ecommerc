<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class StoreSellerProductRequest extends FormRequest
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
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|integer',
            'variants.*.color' => 'required|string',
            'variants.*.size' => 'required|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.sku' => 'nullable|string|distinct',
            'variants.*.primary_image' => "nullable|file|mimes:jpg,jpeg,png,webp|max:$mega",
            'variants.*.gallery_images' => 'nullable|array',
            'variants.*.gallery_images.*' => "file|mimes:jpg,jpeg,png,webp|max:$mega",
            'image_url' => "nullable|file|mimes:jpg,jpeg,png,webp|max:$mega",
            'images' => 'nullable|array',
            'images.*' => "file|mimes:jpg,jpeg,png,webp|max:$mega",
            'discount_type' => 'nullable|string|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_start_at' => 'nullable|date',
            'discount_end_at' => 'nullable|date|after_or_equal:discount_start_at',
        ];
    }
}
