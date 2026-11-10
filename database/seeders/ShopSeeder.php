<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        User::updateOrCreate(
            ['email' => 'admin@shop.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Customer
        User::updateOrCreate(
            ['email' => 'customer@shop.test'],
            [
                'name' => 'Customer',
                'password' => Hash::make('password123'),
                'role' => 'customer',
            ]
        );

        // 3. Categories อย่างน้อย 3 รายการ
        $categories = collect([
            'Electronics',
            'Clothing',
            'Accessories',
        ])->map(function ($name) {
            return Category::firstOrCreate([
                'name' => $name,
            ]);
        });

        // 4. Products อย่างน้อย 30 รายการ
        foreach ($categories as $category) {
            Product::factory()
                ->count(10)
                ->create([
                    'category_id' => $category->id,
                ]);
        }
    }
}
