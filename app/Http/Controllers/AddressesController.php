<?php

namespace App\Http\Controllers;

use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Services\AddressService;
use Illuminate\Http\Request;

class AddressesController extends Controller
{
    public function __construct(
        protected AddressService $addressService
    ) {
    }

    public function index(Request $request)
    {
        return $this->respond($this->addressService->index($request->user()->id));
    }

    public function store(StoreAddressRequest $request)
    {
        return $this->respond($this->addressService->store($request->validated(), $request->user()->id));
    }

    public function show(Request $request, $id)
    {
        return $this->respond($this->addressService->show($id, $request->user()->id));
    }

    public function update(UpdateAddressRequest $request, $id)
    {
        return $this->respond($this->addressService->update($id, $request->validated(), $request->user()->id));
    }

    public function destroy(Request $request, $id)
    {
        return $this->respond($this->addressService->destroy($id, $request->user()->id));
    }
}
