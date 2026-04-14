<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use App\Models\Order;

class OrderController extends Controller
{
   public function show(int $id): JsonResponse
{
    $order = Order::with('items')->find($id);

    if (!$order) {
        return response()->json([
            'message' => 'Order not found'
        ], 404);
    }

    return response()->json([
        'id' => $order->id,
        'patient_name' => $order->patient_name,
        'status' => $order->status,
        'validation_reason' => $order->validation_reason,
        'items' => $order->items,
    ]);
}
}