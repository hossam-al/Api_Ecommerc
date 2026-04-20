<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\brands;
use App\Models\category;
use App\Models\product_variants;
use App\Models\products;
use App\Services\ImageUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class SellerProductWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function createRoles(): array
    {
        return [
            'admin' => Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin']),
            'seller' => Role::create(['name' => 'Seller', 'slug' => 'seller', 'description' => 'Seller']),
        ];
    }

    protected function createCategory(): category
    {
        $suffix = strtoupper(fake()->unique()->bothify('##??'));

        return category::create([
            'name_en' => 'Workflow Category ' . $suffix,
            'name_ar' => 'فئة سير العمل ' . $suffix,
            'description_en' => 'Workflow',
            'description_ar' => 'سير العمل',
            'is_active' => true,
        ]);
    }

    protected function createBrand(): brands
    {
        $suffix = strtoupper(fake()->unique()->bothify('##??'));

        return brands::create([
            'name_en' => 'Workflow Brand ' . $suffix,
            'name_ar' => 'براند سير العمل ' . $suffix,
            'is_active' => true,
        ]);
    }

    protected function createSellerProduct(User $seller, array $overrides = []): products
    {
        $product = products::create(array_merge([
            'user_id' => $seller->id,
            'brand_id' => $this->createBrand()->id,
            'category_id' => $this->createCategory()->id,
            'name_en' => 'Workflow Product',
            'name_ar' => 'منتج سير العمل',
            'description_en' => 'Description',
            'description_ar' => 'وصف',
            'primary_image' => 'https://example.com/workflow.jpg',
            'is_active' => true,
            'review_status' => 'approved',
            'is_featured' => false,
        ], $overrides));

        product_variants::create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'price' => 120,
            'stock' => 7,
            'sku' => 'SKU-' . strtoupper(fake()->unique()->bothify('####??')),
        ]);

        return $product->fresh(['variants']);
    }

    public function test_seller_product_creation_defaults_to_pending_review_and_admin_can_reject_or_approve(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'approved',
            'phone' => '1000000701',
        ]);
        $admin = User::factory()->create([
            'role_id' => $roles['admin']->id,
            'phone' => '1000000702',
        ]);

        $service = Mockery::mock(ImageUploadService::class);
        $service->shouldReceive('processImage')->once()->andReturn('https://example.com/main.jpg');
        $service->shouldReceive('handleMultipleImages')->once()->andReturn(['https://example.com/gallery.jpg']);
        $service->shouldReceive('handleUploadedFiles')->once()->andReturn([]);
        $this->app->instance(ImageUploadService::class, $service);

        Sanctum::actingAs($seller);

        $createResponse = $this->withHeader('Accept', 'application/json')->post('/api/v1/seller/products', [
            'name_en' => 'Seller Product',
            'name_ar' => 'منتج البائع',
            'description_en' => 'Pending product',
            'description_ar' => 'منتج معلق',
            'category_id' => $this->createCategory()->id,
            'brand_id' => $this->createBrand()->id,
            'image_url' => UploadedFile::fake()->create('main.jpg', 100, 'image/jpeg'),
            'variants' => [
                [
                    'color' => 'Black',
                    'size' => 'L',
                    'price' => 150,
                    'stock' => 6,
                    'sku' => 'SELLER-WORKFLOW-001',
                ],
            ],
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.review_status', 'pending')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.is_active', false);

        $productId = $createResponse->json('data.id');

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/admin/products/' . $productId . '/reject')
            ->assertStatus(200)
            ->assertJsonPath('data.review_status', 'rejected')
            ->assertJsonPath('data.status', 'rejected');

        $this->patchJson('/api/v1/admin/products/' . $productId . '/activate')
            ->assertStatus(200)
            ->assertJsonPath('data.review_status', 'approved')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.is_active', true);
    }

    public function test_seller_product_update_resubmits_product_for_review_and_filters_include_rejected(): void
    {
        $roles = $this->createRoles();
        $seller = User::factory()->create([
            'role_id' => $roles['seller']->id,
            'seller_status' => 'approved',
            'phone' => '1000000703',
        ]);

        $approvedProduct = $this->createSellerProduct($seller, [
            'name_en' => 'Approved product',
            'review_status' => 'approved',
            'is_active' => true,
        ]);
        $rejectedProduct = $this->createSellerProduct($seller, [
            'name_en' => 'Rejected product',
            'review_status' => 'rejected',
            'is_active' => false,
        ]);

        Sanctum::actingAs($seller);

        $this->patchJson('/api/v1/seller/products/' . $approvedProduct->id, [
            'name_en' => 'Updated product name',
        ])->assertStatus(200)
            ->assertJsonPath('data.review_status', 'pending')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.is_active', false);

        $rejectedFilter = $this->getJson('/api/v1/seller/products?status=rejected');

        $rejectedFilter->assertStatus(200)
            ->assertJsonPath('filters.status', 'rejected')
            ->assertJsonPath('data.data.0.id', $rejectedProduct->id)
            ->assertJsonPath('data.data.0.review_status', 'rejected');
    }
}
