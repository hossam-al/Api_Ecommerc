<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartsController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {
    }

    public function index(Request $request)
    {
        return $this->respond($this->cartService->index($request->user()->id));
    }

    public function store(StoreCartItemRequest $request)
    {
        return $this->respond($this->cartService->store($request->validated(), $request->user()->id));
    }

    public function update(UpdateCartItemRequest $request, $id)
    {
        return $this->respond($this->cartService->update($id, $request->validated(), $request->user()->id));
    }

    public function destroy(Request $request, $id)
    {
        return $this->respond($this->cartService->destroy($id, $request->user()->id));
    }

    public function clear(Request $request)
    {
        return $this->respond($this->cartService->clear($request->user()->id));
    }
}
