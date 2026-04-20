<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\AssignRoleRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {
    }

    public function index(Request $request)
    {
        return $this->respond($this->roleService->index($request->user()));
    }

    public function store(StoreRoleRequest $request)
    {
        return $this->respond($this->roleService->store($request->validated(), $request->user()));
    }

    public function show(Request $request, string $id)
    {
        return $this->respond($this->roleService->show($id, $request->user()));
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        return $this->respond($this->roleService->update($id, $request->validated(), $request->user()));
    }

    public function destroy(Request $request, string $id)
    {
        return $this->respond($this->roleService->destroy($id, $request->user()));
    }

    public function assignRole(AssignRoleRequest $request)
    {
        return $this->respond($this->roleService->assignRole($request->validated(), $request->user()));
    }
}
