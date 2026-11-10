<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Traits\ApiResponse;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * Get orders of the currently authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['orderItems.product'])
            ->latest('id')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => OrderResource::collection($orders->items()),
            'pagination' => [
                'total' => $orders->total(),
                'count' => $orders->count(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'total_pages' => $orders->lastPage(),
            ],
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Display a specific order of the authenticated customer.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::with(['user', 'orderItems.product'])->findOrFail($id);

        if ($order->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return $this->errorResponse('Order not found or unauthorized', 404);
        }

        return $this->successResponse(
            new OrderResource($order),
            'Order details retrieved successfully'
        );
    }

    /**
     * Create an order with DB transaction, row locking, and price calculation.
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = DB::transaction(function () use ($request) {
                $user = $request->user();
                $items = $request->validated('items');

                $totalAmount = 0;
                $preparedItems = [];

                // 1. ตรวจสอบสต็อกและราคาโดยใช้ lockForUpdate() ป้องกัน Race Condition
                foreach ($items as $itemData) {
                    $productId = $itemData['product_id'];
                    $quantity = (int) $itemData['quantity'];

                    $product = Product::lockForUpdate()->findOrFail($productId);

                    if (! $product->is_active) {
                        throw new \DomainException("สินค้า '{$product->name}' ไม่พร้อมจำหน่ายในขณะนี้");
                    }

                    if ($product->stock < $quantity) {
                        throw new \DomainException("สินค้า '{$product->name}' มีสต็อกไม่เพียงพอ (คงเหลือ {$product->stock} ชิ้น)");
                    }

                    $priceSnapshot = (float) $product->price;
                    $subtotal = round($priceSnapshot * $quantity, 2);
                    $totalAmount += $subtotal;

                    $preparedItems[] = [
                        'product' => $product,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $priceSnapshot,
                    ];
                }

                // 2. สร้าง Order หลัก
                $order = Order::create([
                    'user_id' => $user->id,
                    'total' => $totalAmount,
                    'status' => 'pending',
                ]);

                // 3. สร้าง OrderItems และหักสต็อกสินค้า
                foreach ($preparedItems as $prep) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $prep['product_id'],
                        'quantity' => $prep['quantity'],
                        'price' => $prep['price'],
                    ]);

                    $prep['product']->decrement('stock', $prep['quantity']);
                }

                $order->load(['user', 'orderItems.product']);

                return $order;
            });

            return $this->successResponse(
                new OrderResource($order),
                'Order created successfully',
                201
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to process order: '.$e->getMessage(), 500);
        }
    }

    /**
     * Cancel a pending order and restore product stock.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = Order::with('orderItems')->findOrFail($id);

        if ($order->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return $this->errorResponse('Order not found or unauthorized', 404);
        }

        if ($order->status !== 'pending') {
            return $this->errorResponse('ไม่สามารถยกเลิกคำสั่งซื้อได้ เนื่องจากสถานะไม่ใช่ pending', 422);
        }

        try {
            DB::transaction(function () use ($order) {
                // คืนสต็อกสินค้า
                foreach ($order->orderItems as $item) {
                    $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                $order->update(['status' => 'cancelled']);
            });

            $order->load(['user', 'orderItems.product']);

            return $this->successResponse(
                new OrderResource($order),
                'Order cancelled successfully and stock restored'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to cancel order: '.$e->getMessage(), 500);
        }
    }

    /**
     * List all orders for Admin Web (DataTables).
     */
    public function adminIndex(): JsonResponse
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->latest('id')
            ->get();

        return $this->successResponse(
            OrderResource::collection($orders),
            'All orders retrieved successfully'
        );
    }

    /**
     * Update order status by Admin via AJAX.
     */
    public function adminUpdateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,paid,shipped,completed,cancelled'],
        ]);

        $order = Order::with('orderItems')->findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->input('status');

        if ($oldStatus === $newStatus) {
            return $this->successResponse(
                new OrderResource($order->load(['user', 'orderItems.product'])),
                'Status remains unchanged'
            );
        }

        try {
            DB::transaction(function () use ($order, $oldStatus, $newStatus) {
                // หากเปลี่ยนสถานะเป็น cancelled และสถานะเดิมไม่ใช่ cancelled ให้คืนสต็อก
                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    foreach ($order->orderItems as $item) {
                        $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                        if ($product) {
                            $product->increment('stock', $item->quantity);
                        }
                    }
                }

                $order->update(['status' => $newStatus]);
            });

            $order->load(['user', 'orderItems.product']);

            return $this->successResponse(
                new OrderResource($order),
                "Order status updated to {$newStatus}"
            );
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to update status: '.$e->getMessage(), 500);
        }
    }
}
