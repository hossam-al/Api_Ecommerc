<?php

namespace App\Services;

use App\Models\Coupon;
use App\Support\ApiResponseBuilder;

class CouponService
{
    public function index(): array
    {
        $coupons = Coupon::latest()->get();

        if ($coupons->isEmpty()) {
            return ApiResponseBuilder::error('No coupons found', 404, ['data' => []]);
        }

        return ApiResponseBuilder::success('Coupons retrieved successfully', $coupons);
    }

    public function store(array $validated): array
    {
        $coupon = Coupon::create([
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'value' => $validated['value'],
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
            'max_discount' => $validated['max_discount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return ApiResponseBuilder::success('Coupon created successfully', $coupon, 201);
    }

    public function show(int|string $id): array
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return ApiResponseBuilder::error('Coupon not found', 404);
        }

        return ApiResponseBuilder::success('Coupon retrieved successfully', $coupon);
    }

    public function update(int|string $id, array $validated): array
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return ApiResponseBuilder::error('Coupon not found', 404);
        }

        if (array_key_exists('code', $validated)) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $coupon->update($validated);

        return ApiResponseBuilder::success('Coupon updated successfully', $coupon);
    }

    public function destroy(int|string $id): array
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return ApiResponseBuilder::error('Coupon not found', 404);
        }

        if ($coupon->used_count > 0) {
            return ApiResponseBuilder::error('Cannot delete a used coupon', 400);
        }

        $coupon->delete();

        return ApiResponseBuilder::success('Coupon deleted successfully');
    }

    public function validateCoupon(array $validated): array
    {
        $coupon = Coupon::where('code', $validated['code'])
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return ApiResponseBuilder::error('Invalid coupon', 404);
        }

        if (
            ($coupon->starts_at && now()->lt($coupon->starts_at)) ||
            ($coupon->expires_at && now()->gt($coupon->expires_at))
        ) {
            return ApiResponseBuilder::error('Coupon expired', 400);
        }

        if ($validated['subtotal'] < $coupon->min_order_amount) {
            return ApiResponseBuilder::error('Order not eligible', 400);
        }

        return ApiResponseBuilder::success('Coupon is valid', [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'max_discount' => $coupon->max_discount,
        ]);
    }
}
