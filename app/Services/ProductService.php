<?php

namespace App\Services;

use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Models\product_variants;
use App\Models\products;
use App\Models\products_image;
use App\Notifications\Products\ProductRejectedNotification;
use App\Notifications\Products\ProductSubmittedForReviewNotification;
use App\Support\ApiResponseBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        protected ImageUploadService $imageUploadService,
        protected ProductViewService $productViewService
    ) {
    }

    public function index(Request $request, ?User $user): array
    {
        $query = $this->buildProductsQuery($request);
        $appliedStatus = 'published';

        if ($this->isAdmin($user)) {
            $appliedStatus = $this->applyStatusFilter($query, $request->query('status'));
        } elseif ($this->isSeller($user)) {
            $query->ownedBy($user->id);
            $appliedStatus = $this->applyStatusFilter($query, $request->query('status'));
        } else {
            $query->published();
        }

        $perPage = $this->resolvePerPage($request);
        $products = $query->paginate($perPage);

        if ($products->isEmpty()) {
            return ApiResponseBuilder::error('No products found', 404);
        }

        return ApiResponseBuilder::success(
            'Products retrieved successfully',
            $this->transformProductPaginator($products),
            200,
            [
                'filters' => [
                    'status' => $appliedStatus,
                    'search' => $request->string('search')->toString() ?: null,
                    'per_page' => $perPage,
                ],
            ],
        );
    }

    public function store(array $validated, Request $request, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can add products', 403);
        }

        $this->ensureVariantSkusAreAvailable($validated['variants']);
        $product = $this->createProduct(
            $request,
            $validated,
            $user->id,
            $validated['is_active'] ?? true,
            $validated['is_featured'] ?? false,
            $validated['review_status'] ?? 'approved',
        );

        return ApiResponseBuilder::success(
            'Product added successfully',
            $this->transformProduct($product),
            201,
        );
    }

    public function sellerIndex(Request $request, ?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can view seller products', 403);
        }

        $query = $this->buildProductsQuery($request)->ownedBy($seller->id);
        $appliedStatus = $this->applyStatusFilter($query, $request->query('status'));
        $perPage = $this->resolvePerPage($request);
        $products = $query->paginate($perPage);

        if ($products->isEmpty()) {
            return ApiResponseBuilder::error('No seller products found', 404);
        }

        return ApiResponseBuilder::success(
            'Seller products retrieved successfully',
            $this->transformProductPaginator($products),
            200,
            [
                'filters' => [
                    'status' => $appliedStatus,
                    'search' => $request->string('search')->toString() ?: null,
                    'per_page' => $perPage,
                ],
            ],
        );
    }

    public function sellerStore(array $validated, Request $request, ?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can add products', 403);
        }

        $this->ensureVariantSkusAreAvailable($validated['variants']);
        $product = $this->createProduct($request, $validated, $seller->id, false, false, 'pending');
        $this->notifyAdminsAboutReviewRequest($product, $seller, false);

        return ApiResponseBuilder::success(
            'Product submitted successfully and is pending review',
            $this->transformProduct($product),
            201,
        );
    }

    public function show(int|string $id, Request $request, ?User $user): array
    {
        $product = products::with($this->productRelations())->find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        if ($this->isAdmin($user)) {
            return ApiResponseBuilder::success('Product retrieved successfully', $this->transformProduct($product));
        }

        if ($this->isSeller($user)) {
            if ((int) $product->user_id !== (int) $user->id) {
                return ApiResponseBuilder::error('Product not found', 404);
            }

            return ApiResponseBuilder::success('Product retrieved successfully', $this->transformProduct($product));
        }

        if ($product->review_status !== 'approved' || !$product->is_active) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $this->productViewService->recordView($product, $user, $request);

        return ApiResponseBuilder::success('Product retrieved successfully', $this->transformProduct($product));
    }

    public function sellerShow(int|string $id, ?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can view seller products', 403);
        }

        $product = $this->findSellerProduct($id, $seller->id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        return ApiResponseBuilder::success('Seller product retrieved successfully', $this->transformProduct($product));
    }

    public function update(int|string $id, array $validated, Request $request, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can update products', 403);
        }

        $product = products::with($this->productRelations())->find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        if (array_key_exists('variants', $validated)) {
            $this->ensureVariantSkusAreAvailable($validated['variants'], $product);
        }

        $this->updateProduct($request, $product, $validated, true);

        return ApiResponseBuilder::success('Product updated successfully', $this->transformProduct($product));
    }

    public function sellerUpdate(int|string $id, array $validated, Request $request, ?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can update seller products', 403);
        }

        $product = $this->findSellerProduct($id, $seller->id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        if (array_key_exists('variants', $validated)) {
            $this->ensureVariantSkusAreAvailable($validated['variants'], $product);
        }

        $this->updateProduct($request, $product, $validated, false);
        $this->notifyAdminsAboutReviewRequest($product, $seller, true);

        return ApiResponseBuilder::success(
            'Seller product updated successfully and resubmitted for review',
            $this->transformProduct($product)
        );
    }

    public function deleteImage(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can delete product images', 403);
        }

        $image = products_image::find($id);

        if (!$image) {
            return ApiResponseBuilder::error('Product image not found', 404);
        }

        $image->delete();

        return ApiResponseBuilder::success('Product image deleted successfully');
    }

    public function destroy(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can delete products', 403);
        }

        $product = products::find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        return $this->deleteProduct($product, 'Product deleted successfully');
    }

    public function sellerDestroy(int|string $id, ?User $seller): array
    {
        if (!$this->isSeller($seller)) {
            return ApiResponseBuilder::error('Only Seller accounts can delete seller products', 403);
        }

        $product = $this->findSellerProduct($id, $seller->id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        return $this->deleteProduct($product, 'Seller product deleted successfully');
    }

    public function showPendingProducts(Request $request, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can view pending products', 403);
        }

        $status = strtolower((string) $request->string('status')->toString());
        $status = in_array($status, ['pending', 'rejected', 'all'], true) ? $status : 'pending';
        $sellerProducts = products::query()
            ->whereHas('user', fn($query) => $query->where('role_id', 2));
        $query = $this->buildProductsQuery($request)
            ->whereHas('user', fn($query) => $query->where('role_id', 2));

        match ($status) {
            'pending' => $query->pendingReview(),
            'rejected' => $query->rejected(),
            default => $query->whereIn('review_status', ['pending', 'rejected']),
        };

        $perPage = $this->resolvePerPage($request);
        $products = $query->paginate($perPage);

        if ($products->isEmpty()) {
            return ApiResponseBuilder::error('No product approval requests found', 404);
        }

        return ApiResponseBuilder::success(
            'Product approval requests retrieved successfully',
            $this->transformProductPaginator($products),
            200,
            [
                'filters' => [
                    'status' => $status,
                    'search' => $request->string('search')->toString() ?: null,
                    'per_page' => $perPage,
                ],
                'summary' => [
                    'pending_count' => (clone $sellerProducts)->pendingReview()->count(),
                    'cancelled_count' => (clone $sellerProducts)->rejected()->count(),
                    'all_count' => (clone $sellerProducts)
                        ->whereIn('review_status', ['pending', 'rejected'])
                        ->count(),
                ],
            ],
        );
    }

    public function activateProduct(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can activate products', 403);
        }

        $product = products::with($this->productRelations())->find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $product->update([
            'review_status' => 'approved',
            'is_active' => true,
            'rejection_reason' => null,
        ]);

        return ApiResponseBuilder::success(
            'Product activated successfully',
            $this->transformProduct($product->fresh()->load($this->productRelations())),
        );
    }

    public function rejectProduct(int|string $id, array $validated, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can reject products', 403);
        }

        $product = products::with($this->productRelations())->find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        $product->update([
            'review_status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $validated['reason'],
        ]);

        $product->refresh()->load($this->productRelations());

        if ($product->user) {
            $product->user->notify(new ProductRejectedNotification($product, $validated['reason']));
        }

        return ApiResponseBuilder::success(
            'Product rejected successfully',
            $this->transformProduct($product),
        );
    }

    public function restoreRejectedProduct(int|string $id, ?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can restore rejected products', 403);
        }

        $product = products::with($this->productRelations())->find($id);

        if (!$product) {
            return ApiResponseBuilder::error('Product not found', 404);
        }

        if ($product->review_status !== 'rejected') {
            return ApiResponseBuilder::error('Only rejected products can be restored to review', 422);
        }

        $product->update([
            'review_status' => 'pending',
            'is_active' => false,
            'rejection_reason' => null,
        ]);

        return ApiResponseBuilder::success(
            'Product review request restored successfully',
            $this->transformProduct($product->fresh()->load($this->productRelations())),
        );
    }

    public function deleteAll(?User $user): array
    {
        if (!$this->isAdmin($user)) {
            return ApiResponseBuilder::error('Only Admin can delete all products', 403);
        }

        foreach (products::with(['variants'])->get() as $product) {
            try {
                $product->delete();
            } catch (\Throwable $exception) {
                // Continue deleting remaining records.
            }
        }

        return ApiResponseBuilder::success('All products deletion attempted successfully');
    }

    protected function buildProductsQuery(Request $request): Builder
    {
        $query = products::with($this->productRelations());

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%")
                    ->orWhere('description_ar', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('name_en', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%");
                    })
                    ->orWhereHas('brand', function ($query) use ($search) {
                        $query->where('name_en', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%");
                    })
                    ->orWhereHas('variants', function ($query) use ($search) {
                        $query->where('sku', 'like', "%{$search}%")
                            ->orWhere('price', 'like', "%{$search}%");
                    });
            });
        }

        return $query->latest();
    }

    protected function productRelations(): array
    {
        return ['category', 'images', 'brand', 'variants.images', 'user:id,name,email,phone'];
    }

    protected function applyStatusFilter(Builder $query, mixed $status): string
    {
        $normalizedStatus = strtolower((string) ($status ?? 'all'));

        return match ($normalizedStatus) {
            'published' => tap('published', fn() => $query->published()),
            'pending', 'under_review' => tap('pending', fn() => $query->pendingReview()),
            'rejected' => tap('rejected', fn() => $query->rejected()),
            'inactive' => tap('inactive', fn() => $query->inactive()),
            default => 'all',
        };
    }

    protected function resolvePerPage(Request $request): int
    {
        return max(1, min((int) $request->integer('per_page', 10), 50));
    }

    protected function ensureVariantSkusAreAvailable(array $variants, ?products $product = null): void
    {
        $skus = collect($variants)->pluck('sku')->filter(fn($sku) => filled($sku))->values();

        if ($skus->isEmpty()) {
            return;
        }

        $query = product_variants::whereIn('sku', $skus);

        if ($product) {
            $incomingVariantIds = collect($variants)
                ->pluck('id')
                ->filter(fn($id) => filled($id))
                ->map(fn($id) => (int) $id)
                ->values();

            if ($incomingVariantIds->isNotEmpty()) {
                $query->whereNotIn('id', $incomingVariantIds);
            } else {
                $query->where('product_id', '!=', $product->id);
            }
        }

        $existingSkus = $query->pluck('sku')->all();

        if (!empty($existingSkus)) {
            throw ValidationException::withMessages([
                'variants' => ['The following SKUs are already taken: ' . implode(', ', $existingSkus)],
            ]);
        }
    }

    protected function createProduct(
        Request $request,
        array $validated,
        int $userId,
        bool $isActive,
        bool $isFeatured,
        string $reviewStatus
    ): products {
        $productImages = $this->uploadProductImages($request);
        $variantPayloads = $this->prepareVariantPayloads($request, $validated['variants']);
        $productPrimaryImage = $this->resolveProductPrimaryImage(
            $productImages['primary_image'],
            $productImages['gallery_images'],
            $variantPayloads,
        );
        $discountData = $this->resolveDiscountData($validated, collect($variantPayloads));

        if (!$productPrimaryImage) {
            throw ValidationException::withMessages([
                'image_url' => ['A product cover image or at least one variant image is required.'],
            ]);
        }

        [$normalizedReviewStatus, $normalizedActiveState] = $this->normalizeProductState($reviewStatus, $isActive);

        $product = DB::transaction(function () use (
            $validated,
            $userId,
            $normalizedActiveState,
            $isFeatured,
            $normalizedReviewStatus,
            $productPrimaryImage,
            $productImages,
            $variantPayloads,
            $discountData
        ) {
            $product = products::create([
                'user_id' => $userId,
                'name_en' => $validated['name_en'],
                'name_ar' => $validated['name_ar'],
                'description_en' => $validated['description_en'] ?? null,
                'description_ar' => $validated['description_ar'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'is_active' => $normalizedActiveState,
                'review_status' => $normalizedReviewStatus,
                'rejection_reason' => null,
                'is_featured' => $validated['is_featured'] ?? $isFeatured,
                'primary_image' => $productPrimaryImage,
                'discount_type' => $discountData['discount_type'],
                'discount_value' => $discountData['discount_value'],
                'discount_start_at' => $discountData['discount_start_at'],
                'discount_end_at' => $discountData['discount_end_at'],
            ]);

            $this->createVariants($product, collect($variantPayloads));
            $this->attachLegacyGalleryImages($product, $productImages['gallery_images']);

            return $product;
        });

        return $product->load($this->productRelations());
    }

    protected function updateProduct(Request $request, products $product, array $validated, bool $allowActivationFields): void
    {
        $product->loadMissing(['images', 'variants.images']);

        $productImages = $this->uploadProductImages($request, $product->primary_image);
        $existingVariants = $product->variants->keyBy('id');
        $variantPayloads = array_key_exists('variants', $validated)
            ? $this->prepareVariantPayloads($request, $validated['variants'], $existingVariants)
            : [];

        $productPrimaryImage = $this->resolveProductPrimaryImage(
            $productImages['primary_image'],
            $productImages['gallery_images'],
            $variantPayloads,
            $product->primary_image,
        );
        $variantPrices = array_key_exists('variants', $validated)
            ? collect($variantPayloads)
            : $product->variants;
        $discountData = $this->resolveDiscountData($validated, $variantPrices, $product);

        if ($allowActivationFields) {
            $reviewStatus = $validated['review_status'] ?? $product->review_status;
            $requestedActiveState = $validated['is_active'] ?? $product->is_active;
            [$reviewStatus, $requestedActiveState] = $this->normalizeProductState($reviewStatus, $requestedActiveState);
        } else {
            $reviewStatus = 'pending';
            $requestedActiveState = false;
        }

        $data = [
            'name_en' => $validated['name_en'] ?? $product->name_en,
            'name_ar' => $validated['name_ar'] ?? $product->name_ar,
            'description_en' => $validated['description_en'] ?? $product->description_en,
            'description_ar' => $validated['description_ar'] ?? $product->description_ar,
            'category_id' => array_key_exists('category_id', $validated) ? $validated['category_id'] : $product->category_id,
            'brand_id' => array_key_exists('brand_id', $validated) ? $validated['brand_id'] : $product->brand_id,
            'primary_image' => $productPrimaryImage,
            'review_status' => $reviewStatus,
            'rejection_reason' => $reviewStatus === 'rejected' ? $product->rejection_reason : null,
            'is_active' => $requestedActiveState,
            'is_featured' => $allowActivationFields
                ? ($validated['is_featured'] ?? $product->is_featured)
                : $product->is_featured,
            'discount_type' => $discountData['discount_type'],
            'discount_value' => $discountData['discount_value'],
            'discount_start_at' => $discountData['discount_start_at'],
            'discount_end_at' => $discountData['discount_end_at'],
        ];

        DB::transaction(function () use ($product, $data, $validated, $variantPayloads, $existingVariants, $productImages) {
            $product->update($data);

            if (array_key_exists('variants', $validated)) {
                $this->syncVariants($product, $variantPayloads, $existingVariants);
            }

            $this->attachLegacyGalleryImages($product, $productImages['gallery_images']);
        });

        $product->load($this->productRelations());
    }

    protected function createVariants(products $product, Collection $variants): void
    {
        foreach ($variants as $variant) {
            $createdVariant = $product->variants()->create([
                'color' => $variant['color'],
                'size' => $variant['size'],
                'price' => $variant['price'],
                'stock' => $variant['stock'],
                'sku' => $variant['sku'] ?? null,
                'primary_image' => $variant['primary_image']
                    ?? ($variant['gallery_images'][0] ?? null)
                    ?? $product->primary_image,
            ]);

            $this->attachVariantGalleryImages($product, $createdVariant, $variant['gallery_images'] ?? []);
        }
    }

    protected function attachLegacyGalleryImages(products $product, array $galleryImagePaths): void
    {
        foreach ($galleryImagePaths as $galleryImagePath) {
            if (!$galleryImagePath) {
                continue;
            }

            products_image::create([
                'product_id' => $product->id,
                'url' => $galleryImagePath,
            ]);
        }
    }

    protected function attachVariantGalleryImages(products $product, product_variants $variant, array $galleryImagePaths): void
    {
        foreach ($galleryImagePaths as $galleryImagePath) {
            if (!$galleryImagePath) {
                continue;
            }

            products_image::create([
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'url' => $galleryImagePath,
            ]);
        }
    }

    protected function uploadProductImages(Request $request, ?string $fallbackMainImage = null): array
    {
        try {
            $mainImageUrl = $request->hasFile('image_url')
                ? $this->imageUploadService->processImage($request, 'image_url')
                : $fallbackMainImage;

            $galleryImagePaths = $this->imageUploadService->handleMultipleImages($request);
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                'images' => ['Image upload failed: ' . $exception->getMessage()],
            ]);
        }

        return [
            'primary_image' => $mainImageUrl,
            'gallery_images' => $galleryImagePaths,
        ];
    }

    protected function prepareVariantPayloads(
        Request $request,
        array $variants,
        ?Collection $existingVariants = null
    ): array {
        return collect($variants)
            ->values()
            ->map(function (array $variant, int $index) use ($request, $existingVariants) {
                $variantId = filled($variant['id'] ?? null) ? (int) $variant['id'] : null;
                $existingVariant = $variantId ? $existingVariants?->get($variantId) : null;

                if ($variantId && !$existingVariant) {
                    throw ValidationException::withMessages([
                        "variants.$index.id" => ['The selected variant does not belong to this product.'],
                    ]);
                }

                $galleryImagePaths = $this->uploadVariantGalleryImages($request, $index);
                $primaryImage = $this->uploadVariantPrimaryImage($request, $index, $existingVariant)
                    ?? $this->resolveExistingVariantPrimaryImage($existingVariant, $galleryImagePaths);

                return [
                    'id' => $variantId,
                    'color' => $variant['color'],
                    'size' => $variant['size'],
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                    'sku' => $variant['sku'] ?? null,
                    'primary_image' => $primaryImage,
                    'gallery_images' => $galleryImagePaths,
                ];
            })
            ->all();
    }

    protected function uploadVariantPrimaryImage(
        Request $request,
        int $index,
        ?product_variants $existingVariant = null
    ): ?string {
        try {
            return $request->hasFile("variants.$index.primary_image")
                ? $this->imageUploadService->processUploadedFile($request->file("variants.$index.primary_image"))
                : $existingVariant?->primary_image;
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                "variants.$index.primary_image" => ['Image upload failed: ' . $exception->getMessage()],
            ]);
        }
    }

    protected function uploadVariantGalleryImages(Request $request, int $index): array
    {
        try {
            $files = $request->file("variants.$index.gallery_images", []);
            $files = is_array($files) ? $files : array_filter([$files]);

            return $this->imageUploadService->handleUploadedFiles($files);
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                "variants.$index.gallery_images" => ['Image upload failed: ' . $exception->getMessage()],
            ]);
        }
    }

    protected function resolveExistingVariantPrimaryImage(
        ?product_variants $existingVariant,
        array $galleryImagePaths
    ): ?string {
        if (!empty($galleryImagePaths)) {
            return $galleryImagePaths[0];
        }

        if ($existingVariant?->primary_image) {
            return $existingVariant->primary_image;
        }

        return $existingVariant?->images?->first()?->url;
    }

    protected function resolveProductPrimaryImage(
        ?string $mainImageUrl,
        array $legacyGalleryImagePaths,
        array $variantPayloads,
        ?string $fallbackMainImage = null
    ): ?string {
        if ($mainImageUrl) {
            return $mainImageUrl;
        }

        foreach ($variantPayloads as $variantPayload) {
            if (!empty($variantPayload['primary_image'])) {
                return $variantPayload['primary_image'];
            }

            if (!empty($variantPayload['gallery_images'][0])) {
                return $variantPayload['gallery_images'][0];
            }
        }

        return $legacyGalleryImagePaths[0] ?? $fallbackMainImage;
    }

    protected function syncVariants(products $product, array $variantPayloads, Collection $existingVariants): void
    {
        if ($this->usesVariantIdentifiers($variantPayloads)) {
            $this->syncVariantsByIdentifier($product, $variantPayloads, $existingVariants);

            return;
        }

        $this->replaceAllVariants($product, $variantPayloads);
    }

    protected function syncVariantsByIdentifier(
        products $product,
        array $variantPayloads,
        Collection $existingVariants
    ): void {
        $incomingVariantIds = collect($variantPayloads)
            ->pluck('id')
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id);

        $variantIdsToDelete = $existingVariants->keys()->diff($incomingVariantIds);

        if ($variantIdsToDelete->isNotEmpty()) {
            products_image::where('product_id', $product->id)
                ->whereIn('variant_id', $variantIdsToDelete)
                ->delete();

            $product->variants()->whereIn('id', $variantIdsToDelete)->delete();
        }

        foreach ($variantPayloads as $variantPayload) {
            $variant = !empty($variantPayload['id'])
                ? $existingVariants->get((int) $variantPayload['id'])
                : new product_variants(['product_id' => $product->id]);

            if (!$variant) {
                continue;
            }

            $variant->fill([
                'color' => $variantPayload['color'],
                'size' => $variantPayload['size'],
                'price' => $variantPayload['price'],
                'stock' => $variantPayload['stock'],
                'sku' => $variantPayload['sku'] ?? null,
            ]);

            $variant->primary_image = $variantPayload['primary_image']
                ?? $variant->primary_image
                ?? $variant->images?->first()?->url
                ?? ($variantPayload['gallery_images'][0] ?? null)
                ?? $product->primary_image;

            $variant->product()->associate($product);
            $variant->save();

            $this->attachVariantGalleryImages($product, $variant, $variantPayload['gallery_images'] ?? []);
        }
    }

    protected function replaceAllVariants(products $product, array $variantPayloads): void
    {
        products_image::where('product_id', $product->id)
            ->whereNotNull('variant_id')
            ->delete();

        $product->variants()->delete();
        $this->createVariants($product, collect($variantPayloads));
    }

    protected function usesVariantIdentifiers(array $variantPayloads): bool
    {
        return collect($variantPayloads)->contains(
            fn(array $variantPayload) => filled($variantPayload['id'] ?? null)
        );
    }

    protected function findSellerProduct(int|string $productId, int $sellerId): ?products
    {
        return products::with($this->productRelations())
            ->where('id', $productId)
            ->ownedBy($sellerId)
            ->first();
    }

    protected function normalizeProductState(string $reviewStatus, bool $isActive): array
    {
        $reviewStatus = in_array($reviewStatus, ['pending', 'approved', 'rejected'], true)
            ? $reviewStatus
            : 'pending';

        if ($reviewStatus !== 'approved') {
            return [$reviewStatus, false];
        }

        return [$reviewStatus, $isActive];
    }

    protected function resolveDiscountData(
        array $validated,
        Collection $variantPayloads,
        ?products $product = null
    ): array {
        $discountFields = ['discount_type', 'discount_value', 'discount_start_at', 'discount_end_at'];
        $hasIncomingDiscountField = collect($discountFields)
            ->contains(fn(string $field) => array_key_exists($field, $validated));

        if (!$hasIncomingDiscountField && $product) {
            return [
                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,
                'discount_start_at' => $product->discount_start_at,
                'discount_end_at' => $product->discount_end_at,
            ];
        }

        $discountType = array_key_exists('discount_type', $validated)
            ? $validated['discount_type']
            : $product?->discount_type;
        $discountValue = array_key_exists('discount_value', $validated)
            ? $validated['discount_value']
            : $product?->discount_value;
        $discountStartAt = array_key_exists('discount_start_at', $validated)
            ? $validated['discount_start_at']
            : $product?->discount_start_at;
        $discountEndAt = array_key_exists('discount_end_at', $validated)
            ? $validated['discount_end_at']
            : $product?->discount_end_at;

        if (
            blank($discountType) &&
            ($discountValue === null || $discountValue === '')
        ) {
            return [
                'discount_type' => null,
                'discount_value' => null,
                'discount_start_at' => null,
                'discount_end_at' => null,
            ];
        }

        if (blank($discountType)) {
            throw ValidationException::withMessages([
                'discount_type' => ['A discount type is required when discount value is provided.'],
            ]);
        }

        if ($discountValue === null || $discountValue === '') {
            throw ValidationException::withMessages([
                'discount_value' => ['A discount value is required when discount type is provided.'],
            ]);
        }

        $discountValue = round((float) $discountValue, 2);

        if ($discountType === 'percentage' && $discountValue > 100) {
            throw ValidationException::withMessages([
                'discount_value' => ['Percentage discounts cannot exceed 100.'],
            ]);
        }

        $basePrice = $variantPayloads
            ->min(fn($variant) => (float) data_get($variant, 'price', 0));
        $basePrice = round((float) ($basePrice ?? 0), 2);

        $discountAmount = $discountType === 'percentage'
            ? round(($basePrice * $discountValue) / 100, 2)
            : $discountValue;

        if ($discountAmount > $basePrice) {
            throw ValidationException::withMessages([
                'discount_value' => ['Discount amount cannot reduce the product price below zero.'],
            ]);
        }

        $discountStartAt = filled($discountStartAt) ? Carbon::parse($discountStartAt) : null;
        $discountEndAt = filled($discountEndAt) ? Carbon::parse($discountEndAt) : null;

        if ($discountStartAt && $discountEndAt && $discountEndAt->lt($discountStartAt)) {
            throw ValidationException::withMessages([
                'discount_end_at' => ['The discount end date must be after or equal to the start date.'],
            ]);
        }

        return [
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_start_at' => $discountStartAt,
            'discount_end_at' => $discountEndAt,
        ];
    }

    protected function deleteProduct(products $product, string $message): array
    {
        try {
            $product->delete();
        } catch (\Throwable $exception) {
            return ApiResponseBuilder::error(
                'Product cannot be deleted because it is linked to existing orders',
                409,
            );
        }

        return ApiResponseBuilder::success($message);
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }

    protected function isSeller(?User $user): bool
    {
        return $user && (int) $user->role_id === 2;
    }

    protected function transformProduct(products $product): array
    {
        return (new ProductResource($product->loadMissing($this->productRelations())))->resolve();
    }

    protected function transformProductPaginator($products): array
    {
        return ProductResource::collection($products)->response()->getData(true);
    }

    protected function notifyAdminsAboutReviewRequest(products $product, ?User $seller, bool $isResubmission): void
    {
        $admins = User::query()
            ->where('role_id', 1)
            ->where('is_banned', false)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        foreach ($admins as $admin) {
            $admin->notify(new ProductSubmittedForReviewNotification($product, $seller, $isResubmission));
        }
    }
}





