<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\ListSellerOrdersRequest;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderNotesRequest;
use App\Http\Requests\Orders\UpdateOrderStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    public function index(Request $request)
    {
        return $this->respond($this->orderService->index($request->user()->id));
    }

    public function show($id, Request $request)
    {
        return $this->respond($this->orderService->show($id, $request->user()->id));
    }

    public function store(StoreOrderRequest $request)
    {
        return $this->respond($this->orderService->store($request->validated(), $request->user()));
    }

    public function updateOrderUser(UpdateOrderNotesRequest $request, $id)
    {
        return $this->respond(
            $this->orderService->updateUserOrderNotes($id, $request->validated(), $request->user()->id)
        );
    }

    public function updateOrderAdmin(UpdateOrderNotesRequest $request, $id)
    {
        return $this->respond(
            $this->orderService->updateAdminOrderNotes($id, $request->validated(), $request->user())
        );
    }

    public function destroy($id, Request $request)
    {
        return $this->respond($this->orderService->destroy($id, $request->user()->id));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        return $this->respond(
            $this->orderService->updateStatus($id, $request->validated(), $request->user())
        );
    }

    public function showAllOrders(Request $request)
    {
        return $this->respond($this->orderService->showAllOrders($request->user()));
    }

    public function updateStatusAdmin(UpdateOrderStatusRequest $request, $id)
    {
        return $this->respond(
            $this->orderService->updateStatus($id, $request->validated(), $request->user())
        );
    }

    public function destroyOrder($id, Request $request)
    {
        return $this->respond($this->orderService->destroyOrder($id, $request->user()));
    }

    public function showOrderAdmin($id, Request $request)
    {
        return $this->respond($this->orderService->showOrderAdmin($id, $request->user()));
    }

    public function showSellerOrders(ListSellerOrdersRequest $request)
    {
        return $this->respond($this->orderService->showSellerOrders($request->validated(), $request->user()));
    }

    public function showSellerOrder($id, Request $request)
    {
        return $this->respond($this->orderService->showSellerOrder($id, $request->user()));
    }
}
