<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reviews\ModerateProductReviewRequest;
use App\Http\Requests\Reviews\StoreProductReviewRequest;
use App\Http\Requests\Reviews\UpdateProductReviewRequest;
use App\Services\ProductReviewService;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function __construct(
        protected ProductReviewService $productReviewService
    ) {
    }

    public function index(Request $request, $id)
    {
        return $this->respond(
            $this->productReviewService->index($id, $request->all(), $request->user())
        );
    }

    public function store(StoreProductReviewRequest $request, $id)
    {
        return $this->respond(
            $this->productReviewService->store($id, $request->validated(), $request->user())
        );
    }

    public function update(UpdateProductReviewRequest $request, $id)
    {
        return $this->respond(
            $this->productReviewService->update($id, $request->validated(), $request->user())
        );
    }

    public function destroy(Request $request, $id)
    {
        return $this->respond(
            $this->productReviewService->destroy($id, $request->user())
        );
    }

    public function moderate(ModerateProductReviewRequest $request, $productId, $reviewId)
    {
        return $this->respond(
            $this->productReviewService->moderate(
                $productId,
                $reviewId,
                $request->validated(),
                $request->user()
            )
        );
    }
}
