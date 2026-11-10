<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_api_endpoints(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('customer_token')->plainTextToken;

        // Customer attempts to create product via admin API -> 403 Forbidden
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/admin/products', [
                'name' => 'Hacked Product',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden. Admin access required.');
    }

    public function test_unauthenticated_request_to_protected_api_returns_401(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    public function test_customer_cannot_cancel_order_that_is_not_pending(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('token')->plainTextToken;

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'shipped', // already shipped!
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_product_validation_returns_422_with_detailed_errors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/admin/products', [
                'name' => '', // required
                'price' => -10, // must be >= 0
                'category_id' => 99999, // non-existent
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'category_id']);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Electronics']);

        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/admin/categories', [
                'name' => 'Electronics',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
