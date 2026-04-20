<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'required|exists:addresses,id',
            'coupon_code' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
