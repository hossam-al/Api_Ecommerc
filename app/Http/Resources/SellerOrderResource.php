<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerOrderResource extends JsonResource
{
    public function __construct($resource, protected int $sellerId)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var Order $order */
        $order = $this->resource;

        $sellerItems = $order->items
            ->filter(fn($item) => $item->product && (int) $item->product->user_id === $this->sellerId)
            ->values();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'seller_total' => (float) $sellerItems->sum('subtotal'),
            'seller_items_count' => $sellerItems->count(),
            'notes' => $order->notes,
            'customer' => $order->user ? [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->phone,
            ] : null,
            'address' => [
                'title' => $order->address_title,
                'details' => $order->address_details,
                'governorate' => $order->governorate_name,
            ],
            'items' => $sellerItems->map(function ($item) {
                return [
                    'product' => [
                        'id' => $item->product?->id,
                        'name_en' => $item->product?->name_en,
                        'name_ar' => $item->product?->name_ar,
                        'status' => $item->product?->review_status === 'approved' && $item->product?->is_active
                            ? 'published'
                            : ($item->product?->review_status ?? 'inactive'),
                    ],
                    'variant' => [
                        'id' => $item->variant?->id,
                        'sku' => $item->variant?->sku,
                        'color' => $item->variant?->color,
                        'size' => $item->variant?->size,
                    ],
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->values(),
            'created_at' => $order->created_at?->toDateTimeString(),
        ];
    }
}
