<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiResponseFormatTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomerUser(): User
    {
        $customerRole = Role::create([
            'name' => 'Customer',
            'slug' => 'customer',
            'description' => 'Customer role',
        ]);

        return User::factory()->create([
            'role_id' => $customerRole->id,
            'phone' => '1000000201',
        ]);
    }

    public function test_missing_address_returns_clean_json_response(): void
    {
        $user = $this->createCustomerUser();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/addresses/999999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'status_code' => 404,
                'message' => 'Address not found',
            ])
            ->assertJsonMissing(['exception' => 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException']);
    }

    public function test_empty_addresses_and_cart_return_message_instead_of_empty_arrays(): void
    {
        $user = $this->createCustomerUser();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/addresses')
            ->assertStatus(404)
            ->assertJson([
                'status' => false,
                'status_code' => 404,
                'message' => 'No addresses found',
                'data' => [],
            ]);

        $this->getJson('/api/v1/cart')
            ->assertStatus(404)
            ->assertJson([
                'status' => false,
                'status_code' => 404,
                'message' => 'Cart is empty',
                'data' => [
                    'items' => [],
                    'subtotal' => 0,
                ],
            ]);
    }
}
