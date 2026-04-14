<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'patient_name' => $data['patient_name'],
                'status' => 'pending',
                'validation_reason' => null,
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                ]);
            }

            return $order->load('items');
        });
    }
}