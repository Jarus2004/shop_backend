<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_active_products_with_pagination_10(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(15)->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);
        // inactive product
        Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 15)
            ->assertJsonCount(10, 'data');
    }

    public function test_can_search_and_filter_products_by_category(): void
    {
        $cat1 = Category::factory()->create(['name' => 'Phones']);
        $cat2 = Category::factory()->create(['name' => 'Laptops']);

        Product::factory()->create([
            'category_id' => $cat1->id,
            'name' => 'iPhone 15',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $cat1->id,
            'name' => 'Samsung S24',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $cat2->id,
            'name' => 'MacBook Pro',
            'is_active' => true,
        ]);

        // Search iPhone
        $searchRes = $this->getJson('/api/products?search=iPhone');
        $searchRes->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'iPhone 15');

        // Filter by category
        $filterRes = $this->getJson("/api/products?category_id={$cat2->id}");
        $filterRes->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'MacBook Pro');
    }

    public function test_admin_can_create_update_and_soft_delete_and_restore_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;
        $category = Category::factory()->create();

        // 1. Create with validation
        $createRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/admin/products', [
                'category_id' => $category->id,
                'name' => 'Sony Headphones',
                'description' => 'Noise Cancelling',
                'price' => 7990.00,
                'stock' => 20,
                'is_active' => true,
            ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('data.name', 'Sony Headphones');

        $productId = $createRes->json('data.id');

        // 2. Soft Delete
        $deleteRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/admin/products/{$productId}");

        $deleteRes->assertStatus(200);

        // Check product is soft deleted
        $this->assertSoftDeleted('products', ['id' => $productId]);

        // Product should not appear in customer list
        $customerListRes = $this->getJson('/api/products');
        $customerListRes->assertJsonMissing(['id' => $productId]);

        // 3. Listed in trashed
        $trashedRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/products/trashed');
        $trashedRes->assertStatus(200)
            ->assertJsonPath('data.0.id', $productId);

        // 4. Restore
        $restoreRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/admin/products/{$productId}/restore");
        $restoreRes->assertStatus(200);

        $this->assertNotSoftDeleted('products', ['id' => $productId]);
    }
}
