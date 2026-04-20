<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        RateLimiter::clear('login');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_login_with_incorrect_credentials(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401);
    }

    public function test_banned_user_cannot_login(): void
    {
        User::create([
            'name' => 'Banned User',
            'email' => 'banned@example.com',
            'phone' => '1234567891',
            'password' => Hash::make('password123'),
            'is_banned' => true,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'banned@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'This account has been banned.',
            ]);
    }

    public function test_rate_limiting_on_failed_login_attempts(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
        ]);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
            $response->assertStatus(401);
        }

        // The 6th attempt should be rate limited
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(429);
    }
}
