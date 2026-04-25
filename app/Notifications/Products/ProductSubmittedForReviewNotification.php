<?php

namespace App\Notifications\Products;

use App\Models\User;
use App\Models\products;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductSubmittedForReviewNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected products $product,
        protected ?User $seller,
        protected bool $isResubmission = false
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_review_request',
            'title' => $this->isResubmission
                ? 'Product resubmitted for review'
                : 'New product approval request',
            'message' => $this->isResubmission
                ? 'A seller updated a product and sent it back to the approval queue.'
                : 'A seller submitted a new product that needs publish approval.',
            'action_label' => 'Open approval queue',
            'action_path' => '/app/product-approvals',
            'product' => [
                'id' => $this->product->id,
                'name_en' => $this->product->name_en,
                'name_ar' => $this->product->name_ar,
                'primary_image' => $this->product->primary_image,
                'review_status' => $this->product->review_status,
            ],
            'sender' => $this->seller ? [
                'id' => $this->seller->id,
                'name' => $this->seller->name,
                'email' => $this->seller->email,
                'phone' => $this->seller->phone,
            ] : null,
            'metadata' => [
                'is_resubmission' => $this->isResubmission,
                'review_status' => $this->product->review_status,
            ],
        ];
    }
}
