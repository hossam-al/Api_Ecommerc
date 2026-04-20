<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brands\StoreBrandRequest;
use App\Http\Requests\Brands\UpdateBrandRequest;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    public function __construct(
        protected BrandService $brandService
    ) {
    }

    public function index(Request $request)
    {
        return $this->respond($this->brandService->index($request));
    }

    public function store(StoreBrandRequest $request)
    {
        return $this->respond($this->brandService->store($request->validated(), $request->user()));
    }

    public function show($id)
    {
        return $this->respond($this->brandService->show($id));
    }

    public function update(UpdateBrandRequest $request, $id)
    {
        return $this->respond($this->brandService->update($id, $request->validated(), $request->user()));
    }

    public function destroy($id, Request $request)
    {
        return $this->respond($this->brandService->destroy($id, $request->user()));
    }
}
