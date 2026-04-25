<?php

namespace App\Services;

use App\Models\brands as Brand;
use App\Models\User;
use App\Support\ApiResponseBuilder;
use Illuminate\Http\Request;

class BrandService
{
    public function index(Request $request): array
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $brands = $query->get();

        if ($brands->isEmpty()) {
            return ApiResponseBuilder::error('No brands found', 404);
        }

        return ApiResponseBuilder::success(
            'Brands retrieved successfully',
            $brands,
            200,
            ['results' => $brands->count()],
        );
    }

    public function store(array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can create brands', 403);
        }

        $brand = Brand::create([
            'name_en' => $validated['name_en'],
            'name_ar' => $validated['name_ar'],
            'logo' => $validated['logo'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return ApiResponseBuilder::success('Brand created successfully', $brand, 201);
    }

    public function show(int|string $id): array
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return ApiResponseBuilder::error('Brand not found', 404);
        }

        return ApiResponseBuilder::success('Brand retrieved successfully', $brand);
    }

    public function update(int|string $id, array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can update brands', 403);
        }

        $brand = Brand::find($id);

        if (!$brand) {
            return ApiResponseBuilder::error('Brand not found', 404);
        }

        $brand->update([
            'name_en' => $validated['name_en'] ?? $brand->name_en,
            'name_ar' => $validated['name_ar'] ?? $brand->name_ar,
            'logo' => $validated['logo'] ?? $brand->logo,
            'is_active' => $validated['is_active'] ?? $brand->is_active,
        ]);

        return ApiResponseBuilder::success('Brand updated successfully', $brand);
    }

    public function destroy(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can delete brands', 403);
        }

        $brand = Brand::find($id);

        if (!$brand) {
            return ApiResponseBuilder::error('Brand not found', 404);
        }

        $brand->delete();

        return ApiResponseBuilder::success('Brand deleted successfully', $brand);
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }
}
