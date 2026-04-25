<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminProducts\StoreAdminProductRequest;
use App\Http\Requests\AdminProducts\UpdateAdminProductRequest;
use App\Services\AdminProductService;
use Illuminate\Http\Request;

class AdminProductsController extends Controller
{
    public function __construct(
        protected AdminProductService $adminProductService
    ) {
    }

    public function index(Request $request)
    {
        return $this->respond($this->adminProductService->index($request, $request->user()));
    }

    public function store(StoreAdminProductRequest $request)
    {
        return $this->respond(
            $this->adminProductService->store($request->validated(), $request, $request->user())
        );
    }

    public function show(Request $request, $id)
    {
        return $this->respond($this->adminProductService->show($id, $request->user()));
    }

    public function update(UpdateAdminProductRequest $request, $id)
    {
        return $this->respond(
            $this->adminProductService->update($id, $request->validated(), $request, $request->user())
        );
    }

    public function destroy(Request $request, $id)
    {
        return $this->respond($this->adminProductService->destroy($id, $request->user()));
    }

    public function DeleteAll(Request $request)
    {
        return $this->respond($this->adminProductService->deleteAll($request->user()));
    }
}
