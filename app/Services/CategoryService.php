<?php

namespace App\Services;

use App\Models\category;
use App\Models\User;
use App\Support\ApiResponseBuilder;
use Illuminate\Http\Request;

class CategoryService
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {
    }

    public function index(Request $request): array
    {
        $query = category::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $categories = $query->get();

        if ($categories->isEmpty()) {
            return ApiResponseBuilder::error('No categories found', 404);
        }

        return ApiResponseBuilder::success(
            'Categories retrieved successfully',
            $categories,
            200,
            ['results' => $categories->count()],
        );
    }

    public function store(array $validated, Request $request, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only the Owner can create categories', 403);
        }

        $category = category::create([
            'name_en' => $validated['name_en'],
            'name_ar' => $validated['name_ar'],
            'description_en' => $validated['description_en'] ?? null,
            'description_ar' => $validated['description_ar'] ?? null,
            'image_url' => $this->uploadImage($request),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return ApiResponseBuilder::success('Category created successfully', $category, 201);
    }

    public function show(int|string $id): array
    {
        $category = category::find($id);

        if (!$category) {
            return ApiResponseBuilder::error('Category not found', 404);
        }

        return ApiResponseBuilder::success('Category retrieved successfully', $category);
    }

    public function update(int|string $id, array $validated, Request $request, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only the Owner can update categories', 403);
        }

        $category = category::find($id);

        if (!$category) {
            return ApiResponseBuilder::error('Category not found', 404);
        }

        $category->update([
            'name_en' => $validated['name_en'] ?? $category->name_en,
            'name_ar' => $validated['name_ar'] ?? $category->name_ar,
            'description_en' => $validated['description_en'] ?? $category->description_en,
            'description_ar' => $validated['description_ar'] ?? $category->description_ar,
            'image_url' => $request->hasFile('image_url')
                ? $this->uploadImage($request)
                : $category->image_url,
            'is_active' => $validated['is_active'] ?? $category->is_active,
        ]);

        return ApiResponseBuilder::success('Category updated successfully', $category);
    }

    public function destroy(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only the Owner can delete categories', 403);
        }

        $category = category::find($id);

        if (!$category) {
            return ApiResponseBuilder::error('Category not found', 404);
        }

        $category->delete();

        return ApiResponseBuilder::success('Category deleted successfully', $category);
    }

    public function deleteAll(?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only the Owner can delete all categories', 403);
        }

        foreach (category::all() as $category) {
            try {
                $category->delete();
            } catch (\Throwable $exception) {
                // Continue deleting remaining records.
            }
        }

        return ApiResponseBuilder::success('All categories deletion attempted (Owner)');
    }

    protected function uploadImage(Request $request): ?string
    {
        if (!$request->hasFile('image_url')) {
            return null;
        }

        return $this->imageUploadService->processImage($request, 'image_url');
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }
}
