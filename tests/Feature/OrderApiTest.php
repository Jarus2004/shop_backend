<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_order_with_stock_deduction_and_server_price(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('token')->plainTextToken;

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 1500.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total', 3000) // 1500 * 2 calculated by server!
            ->assertJsonPath('data.status', 'pending');

        // Stock decreased from 10 to 8
        $this->assertEquals(8, $product->fresh()->stock);

        // Order item recorded price snapshot
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 1500.00,
        ]);
    }

    public function test_create_order_fails_when_stock_is_insufficient(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('token')->plainTextToken;

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 500.00,
            'stock' => 3, // only 3 in stock
            'is_active' => true,
        ]);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5, // request 5
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        // Stock should remain unchanged
        $this->assertEquals(3, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_restored(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('token')->plainTextToken;

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 5,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'total' => 1000.00,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 500.00,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        // Stock restored from 5 to 7
        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $customerA = User::factory()->create();
        $customerB = User::factory()->create();
        $tokenA = $customerA->createToken('token')->plainTextToken;

        $orderB = Order::create([
            'user_id' => $customerB->id,
            'total' => 500.00,
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->getJson("/api/orders/{$orderB->id}");

        $response->assertStatus(404);
    }

    public function test_admin_can_update_order_status_via_patch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('token')->plainTextToken;

        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'shipped',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'shipped');

        $this->assertEquals('shipped', $order->fresh()->status);
    }
}
