<?php

namespace App\Http\Controllers;

use App\Http\Requests\Governorates\StoreGovernorateRequest;
use App\Http\Requests\Governorates\UpdateGovernorateRequest;
use App\Services\GovernorateService;

class GovernoratesController extends Controller
{
    public function __construct(
        protected GovernorateService $governorateService
    ) {
    }

    public function index()
    {
        return $this->respond($this->governorateService->index());
    }

    public function store(StoreGovernorateRequest $request)
    {
        return $this->respond($this->governorateService->store($request->validated()));
    }

    public function show($id)
    {
        return $this->respond($this->governorateService->show($id));
    }

    public function update(UpdateGovernorateRequest $request, $id)
    {
        return $this->respond($this->governorateService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return $this->respond($this->governorateService->destroy($id));
    }
}
