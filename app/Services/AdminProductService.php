<?php

namespace App\Services;

use App\Models\AdminProduct;
use App\Models\User;
use App\Support\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductService
{
    public function index(Request $request, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can manage admin products', 403);
        }

        $query = AdminProduct::with('category');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return ApiResponseBuilder::error('No admin products found', 404);
        }

        return ApiResponseBuilder::success(
            'Admin products retrieved successfully',
            $products,
            200,
            ['results' => $products->count()],
        );
    }

    public function store(array $validated, Request $request, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can manage admin products', 403);
        }

        $product = AdminProduct::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image_url' => $this->uploadImage($request),
            'sku' => $validated['sku'] ?? strtoupper(Str::random(10)),
            'stock' => $validated['stock'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
            'category_id' => $validated['category_id'] ?? null,
            'user_id' => $user->id,
        ]);

        return ApiResponseBuilder::success('Admin product added successfully', $product, 201);
    }

    public function show(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can manage admin products', 403);
        }

        $product = AdminProduct::with('category')->find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        return ApiResponseBuilder::success('Admin product retrieved successfully', $product);
    }

    public function update(int|string $id, array $validated, Request $request, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can manage admin products', 403);
        }

        $product = AdminProduct::find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $product->update([
            'name' => $validated['name'] ?? $product->name,
            'description' => $validated['description'] ?? $product->description,
            'price' => $validated['price'] ?? $product->price,
            'stock' => $validated['stock'] ?? $product->stock,
            'category_id' => $validated['category_id'] ?? $product->category_id,
            'is_active' => $validated['is_active'] ?? $product->is_active,
            'is_featured' => $validated['is_featured'] ?? $product->is_featured,
            'image_url' => $request->hasFile('image_url')
                ? $this->replaceImage($request, $product->image_url)
                : $product->image_url,
            'sku' => $validated['sku'] ?? $product->sku,
        ]);

        return ApiResponseBuilder::success('Product updated successfully', $product->load('category'));
    }

    public function destroy(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can manage admin products', 403);
        }

        $product = AdminProduct::find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $this->deleteImageIfExists($product->image_url);
        $product->delete();

        return ApiResponseBuilder::success('Admin product deleted successfully');
    }

    public function deleteAll(?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Super Admin can manage admin products', 403);
        }

        foreach (AdminProduct::all() as $product) {
            $this->deleteImageIfExists($product->image_url);
            $product->delete();
        }

        return ApiResponseBuilder::success('All admin products deleted successfully');
    }

    protected function uploadImage(Request $request): ?string
    {
        if (!$request->hasFile('image_url')) {
            return null;
        }

        $image = $request->file('image_url');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $location = public_path('upload');
        $image->move($location, $imageName);

        return url('upload/' . $imageName);
    }

    protected function replaceImage(Request $request, ?string $currentImage): ?string
    {
        $this->deleteImageIfExists($currentImage);

        return $this->uploadImage($request);
    }

    protected function deleteImageIfExists(?string $imageUrl): void
    {
        if (!$imageUrl) {
            return;
        }

        $imagePath = public_path('upload/' . basename($imageUrl));

        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }
}
