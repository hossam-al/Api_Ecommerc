<?php

namespace App\Notifications\Products;

use App\Models\products;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected products $product,
        protected string $reason
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_rejected',
            'title' => 'Product rejected',
            'message' => 'Your product was rejected during review. Update it and submit again when ready.',
            'reason' => $this->reason,
            'action_label' => 'Review product',
            'product' => [
                'id' => $this->product->id,
                'name_en' => $this->product->name_en,
                'name_ar' => $this->product->name_ar,
                'primary_image' => $this->product->primary_image,
                'review_status' => $this->product->review_status,
            ],
            'metadata' => [
                'review_status' => $this->product->review_status,
                'is_active' => (bool) $this->product->is_active,
            ],
        ];
    }
}
