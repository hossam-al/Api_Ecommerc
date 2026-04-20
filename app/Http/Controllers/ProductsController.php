<?php

namespace App\Http\Controllers;

use App\Http\Requests\Products\ListSellerProductsRequest;
use App\Http\Requests\Products\RejectProductRequest;
use App\Http\Requests\Products\StoreAdminProductRequest;
use App\Http\Requests\Products\StoreSellerProductRequest;
use App\Http\Requests\Products\UpdateAdminProductRequest;
use App\Http\Requests\Products\UpdateSellerProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {
    }

    public function index(Request $request)
    {
        return $this->respond($this->productService->index($request, $request->user()));
    }

    public function store(StoreAdminProductRequest $request)
    {
        return $this->respond(
            $this->productService->store($request->validated(), $request, $request->user())
        );
    }

    public function sellerIndex(ListSellerProductsRequest $request)
    {
        return $this->respond($this->productService->sellerIndex($request, $request->user()));
    }

    public function sellerStore(StoreSellerProductRequest $request)
    {
        return $this->respond(
            $this->productService->sellerStore($request->validated(), $request, $request->user())
        );
    }

    public function show($id, Request $request)
    {
        return $this->respond($this->productService->show($id, $request, $request->user()));
    }

    public function sellerShow($id, Request $request)
    {
        return $this->respond($this->productService->sellerShow($id, $request->user()));
    }

    public function update(UpdateAdminProductRequest $request, $id)
    {
        return $this->respond(
            $this->productService->update($id, $request->validated(), $request, $request->user())
        );
    }

    public function sellerUpdate(UpdateSellerProductRequest $request, $id)
    {
        return $this->respond(
            $this->productService->sellerUpdate($id, $request->validated(), $request, $request->user())
        );
    }

    public function deleteImage($id, Request $request)
    {
        return $this->respond($this->productService->deleteImage($id, $request->user()));
    }

    public function destroy($id, Request $request)
    {
        return $this->respond($this->productService->destroy($id, $request->user()));
    }

    public function sellerDestroy($id, Request $request)
    {
        return $this->respond($this->productService->sellerDestroy($id, $request->user()));
    }

    public function showPendingProducts(Request $request)
    {
        return $this->respond($this->productService->showPendingProducts($request, $request->user()));
    }

    public function activateProduct($id, Request $request)
    {
        return $this->respond($this->productService->activateProduct($id, $request->user()));
    }

    public function rejectProduct(RejectProductRequest $request, $id)
    {
        return $this->respond(
            $this->productService->rejectProduct($id, $request->validated(), $request->user())
        );
    }

    public function restoreRejectedProduct($id, Request $request)
    {
        return $this->respond($this->productService->restoreRejectedProduct($id, $request->user()));
    }

    public function DeleteAll(Request $request)
    {
        return $this->respond($this->productService->deleteAll($request->user()));
    }
}


