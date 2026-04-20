<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class ListSellerOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:pending,processing,completed,cancelled',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort' => 'nullable|string|in:latest,oldest',
            'per_page' => 'nullable|integer|min:1|max:50',
        ];
    }
}
