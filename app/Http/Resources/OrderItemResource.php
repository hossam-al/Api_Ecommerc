<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'products' => [
                'id' => $this->product->id,
                'name_en' => $this->product->name_en,
                'name_ar' => $this->product->name_ar,
            ],
            'variant' => [
                'id' => $this->variant?->id,
                'sku' => $this->variant?->sku,
                'color' => $this->variant?->color,
                'size' => $this->variant?->size,
            ],
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
        ];
    }
}
