<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of categories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Display the specified category.
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::withCount('products')->findOrFail($id);

        return $this->successResponse(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return $this->successResponse(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    /**
     * Remove the specified category.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        if ($category->products()->exists()) {
            return $this->errorResponse(
                'ไม่สามารถลบหมวดหมู่นี้ได้เนื่องจากยังมีสินค้าอยู่ในหมวดหมู่ กรุณาย้ายหรือลบสินค้าก่อน',
                422
            );
        }

        $category->delete();

        return $this->successResponse(null, 'ลบหมวดหมู่เรียบร้อยแล้ว');
    }
}
