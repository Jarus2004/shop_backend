<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of active products for customers (paginated 10 per page).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->latest('id')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => ProductResource::collection($products->items()),
            'pagination' => [
                'total' => $products->total(),
                'count' => $products->count(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'total_pages' => $products->lastPage(),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Display all products for admin (including inactive) for DataTables.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->latest('id')->get();

        return $this->successResponse(
            ProductResource::collection($products),
            'Admin products retrieved successfully'
        );
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->findOrFail($id);

        return $this->successResponse(
            new ProductResource($product),
            'Product details retrieved successfully'
        );
    }

    /**
     * Store a newly created product (Admin).
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($data);
        $product->load('category');

        return $this->successResponse(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    /**
     * Update the specified product (Admin).
     */
    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $product->update($data);
        $product->load('category');

        return $this->successResponse(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    /**
     * Soft delete the specified product (Admin).
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return $this->successResponse(null, 'Product deleted successfully (Soft Delete)');
    }

    /**
     * List trashed products (Admin).
     */
    public function trashed(): JsonResponse
    {
        $trashedProducts = Product::onlyTrashed()->with('category')->latest('deleted_at')->get();

        return $this->successResponse(
            ProductResource::collection($trashedProducts),
            'Trashed products retrieved successfully'
        );
    }

    /**
     * Restore a soft-deleted product (Admin).
     */
    public function restore(int $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        $product->load('category');

        return $this->successResponse(
            new ProductResource($product),
            'Product restored successfully'
        );
    }
}
