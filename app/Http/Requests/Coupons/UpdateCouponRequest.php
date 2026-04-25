<?php

namespace App\Http\Requests\Coupons;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $coupon = $this->route('coupon') ?? $this->route('id');
        $couponId = is_object($coupon) ? $coupon->id : $coupon;

        return [
            'code' => 'sometimes|required|string|unique:coupons,code,' . $couponId,
            'type' => 'sometimes|required|in:fixed,percent',
            'value' => 'sometimes|required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ];
    }
}
