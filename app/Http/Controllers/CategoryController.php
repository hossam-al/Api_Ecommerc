<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {
    }

    public function index(Request $request)
    {
        return $this->respond($this->categoryService->index($request));
    }

    public function store(StoreCategoryRequest $request)
    {
        return $this->respond(
            $this->categoryService->store($request->validated(), $request, $request->user())
        );
    }

    public function show($id)
    {
        return $this->respond($this->categoryService->show($id));
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        return $this->respond(
            $this->categoryService->update($id, $request->validated(), $request, $request->user())
        );
    }

    public function destroy($id, Request $request)
    {
        return $this->respond($this->categoryService->destroy($id, $request->user()));
    }

    public function DeleteAll(Request $request)
    {
        return $this->respond($this->categoryService->deleteAll($request->user()));
    }
}
