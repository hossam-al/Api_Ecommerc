<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class ListSellerProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:all,published,pending,under_review,rejected,inactive',
            'per_page' => 'nullable|integer|min:1|max:50',
            'search' => 'nullable|string|max:255',
        ];
    }
}
