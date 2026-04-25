<?php

namespace App\Services;

use App\Http\Resources\CartItemResource;
use App\Models\Cart;
use App\Models\product_variants;
use App\Support\ApiResponseBuilder;

class CartService
{
    public function index(int $userId): array
    {
        $cart = Cart::with(['product.images', 'variant.images'])
            ->where('user_id', $userId)
            ->get();

        if ($cart->isEmpty()) {
            return ApiResponseBuilder::error('Cart is empty', 404, [
                'data' => [
                    'items' => [],
                    'subtotal' => 0,
                ],
            ]);
        }

        return ApiResponseBuilder::success('Cart retrieved successfully', [
            'items' => CartItemResource::collection($cart)->resolve(),
            'items_count' => $cart->count(),
            'total_quantity' => $cart->sum('quantity'),
            'subtotal' => $cart->sum('subtotal'),
        ]);
    }

    public function store(array $validated, int $userId): array
    {
        $variant = product_variants::with(['product.images', 'images'])->findOrFail($validated['variant_id']);
        $product = $variant->product;

        if (!$product || !$product->is_active) {
            return ApiResponseBuilder::error('This product is currently unavailable', 400);
        }

        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant->id)
            ->first();

        $requestedQuantity = (int) $validated['quantity'];
        $nextQuantity = $cartItem ? $cartItem->quantity + $requestedQuantity : $requestedQuantity;

        if ($variant->stock < $nextQuantity) {
            return ApiResponseBuilder::error('Insufficient stock', 400, [
                'data' => [
                    'available_stock' => $variant->stock,
                    'requested_quantity' => $nextQuantity,
                    'variant_id' => $variant->id,
                ],
            ]);
        }

        if ($cartItem) {
            $cartItem->quantity = $nextQuantity;
            $cartItem->price = $variant->price;
            $cartItem->subtotal = $cartItem->quantity * $cartItem->price;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id' => $userId,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'quantity' => $requestedQuantity,
                'price' => $variant->price,
                'subtotal' => $variant->price * $requestedQuantity,
            ]);
        }

        return ApiResponseBuilder::success(
            'Item added to cart',
            (new CartItemResource($cartItem->load(['product.images', 'variant.images'])))->resolve(),
            201,
        );
    }

    public function update(int|string $id, array $validated, int $userId): array
    {
        $cartItem = Cart::where('id', $id)
            ->where('user_id', $userId)
            ->with(['product.images', 'variant.images'])
            ->firstOrFail();

        if (!$cartItem->product || !$cartItem->product->is_active) {
            return ApiResponseBuilder::error('This product is currently unavailable', 400);
        }

        if (!$cartItem->variant || $cartItem->variant->stock < $validated['quantity']) {
            return ApiResponseBuilder::error('Insufficient stock', 400, [
                'data' => [
                    'available_stock' => $cartItem->variant?->stock ?? 0,
                    'requested_quantity' => (int) $validated['quantity'],
                    'variant_id' => $cartItem->variant_id,
                ],
            ]);
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->price = $cartItem->variant->price;
        $cartItem->subtotal = $cartItem->price * $validated['quantity'];
        $cartItem->save();

        return ApiResponseBuilder::success(
            'Cart item updated',
            (new CartItemResource($cartItem->fresh()->load(['product.images', 'variant.images'])))->resolve(),
        );
    }

    public function destroy(int|string $id, int $userId): array
    {
        $cartItem = Cart::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $cartItem->delete();

        return ApiResponseBuilder::success('Item removed from cart');
    }

    public function clear(int $userId): array
    {
        Cart::where('user_id', $userId)->delete();

        return ApiResponseBuilder::success('Cart cleared');
    }
}
