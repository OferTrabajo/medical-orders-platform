<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ValidateOrderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::with('items')->find($this->orderId);

        if (!$order) {
            return;
        }

        $errors = [];

        foreach ($order->items as $item) {
            if ($item->type === 'medication' && $item->price > 20000) {
                $errors[] = "Medication '{$item->name}' exceeds the allowed price limit.";
            }
        }

        if (!empty($errors)) {
            $order->update([
                'status' => 'rejected',
                'validation_reason' => implode(' ', $errors),
            ]);

            return;
        }

        $order->update([
            'status' => 'approved',
            'validation_reason' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            return;
        }

        $order->update([
            'status' => 'failed',
            'validation_reason' => $exception?->getMessage() ?? 'Unknown queue error',
        ]);
    }
}