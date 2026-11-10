<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories_with_product_count(): void
    {
        $category = Category::factory()->create(['name' => 'Fashion']);
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Fashion')
            ->assertJsonPath('data.0.products_count', 1);
    }

    public function test_admin_can_create_and_update_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        // Create
        $createRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/admin/categories', ['name' => 'Gadgets']);

        $createRes->assertStatus(201)
            ->assertJsonPath('data.name', 'Gadgets');

        $categoryId = $createRes->json('data.id');

        // Update
        $updateRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/admin/categories/{$categoryId}", ['name' => 'Smart Gadgets']);

        $updateRes->assertStatus(200)
            ->assertJsonPath('data.name', 'Smart Gadgets');
    }

    public function test_cannot_delete_category_that_has_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_can_delete_category_without_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin_token')->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
