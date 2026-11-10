<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receives_token(): void
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'john@example.com')
            ->assertJsonPath('data.user.role', 'customer')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'testuser@example.com');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_view_profile_and_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        // View profile
        $profileResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);

        // Logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify token was deleted from database
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Reset auth guards to simulate a separate HTTP request
        \Illuminate\Support\Facades\Auth::forgetGuards();

        // Profile after logout should fail with 401
        $afterLogoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/profile');

        $afterLogoutResponse->assertStatus(401);
    }
}
