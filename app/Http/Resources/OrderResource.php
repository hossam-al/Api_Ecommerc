<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return  throw new \Exception("Error Processing Request", 1);;
        }
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'total' => $this->total_amount,
            'shipping_cost' => $this->shipping_cost,
            'discount' => $this->discount_amount,
            'coupon_code' => $this->coupon_code,
            'notes' => $this->notes,

            'user' => $this->whenLoaded('user') ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ] : null,

            'address' => [
                'title' => $this->address_title,
                'details' => $this->address_details,
                'governorate' => $this->governorate_name,
            ],

            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
