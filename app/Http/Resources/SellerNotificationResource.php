<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payload = is_array($this->data) ? $this->data : [];
        $product = is_array($payload['product'] ?? null) ? $payload['product'] : null;

        return [
            'id' => $this->id,
            'type' => $payload['type'] ?? class_basename($this->type),
            'title' => $payload['title'] ?? 'Notification',
            'message' => $payload['message'] ?? null,
            'reason' => $payload['reason'] ?? null,
            'action_label' => $payload['action_label'] ?? null,
            'action_path' => $payload['action_path'] ?? null,
            'product' => $product ? [
                'id' => $product['id'] ?? null,
                'name_en' => $product['name_en'] ?? null,
                'name_ar' => $product['name_ar'] ?? null,
                'primary_image' => $product['primary_image'] ?? null,
                'review_status' => $product['review_status'] ?? null,
            ] : null,
            'sender' => is_array($payload['sender'] ?? null) ? [
                'id' => $payload['sender']['id'] ?? null,
                'name' => $payload['sender']['name'] ?? null,
                'email' => $payload['sender']['email'] ?? null,
                'phone' => $payload['sender']['phone'] ?? null,
            ] : null,
            'metadata' => is_array($payload['metadata'] ?? null) ? $payload['metadata'] : null,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
