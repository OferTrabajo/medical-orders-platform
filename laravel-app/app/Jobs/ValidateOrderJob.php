<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
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

        $payload = [
            'items' => $order->items->map(function ($item) {
                return [
                    'type' => $item->type,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                ];
            })->values()->toArray(),
        ];

        $response = Http::timeout(10)
            ->acceptJson()
            ->post(config('services.nest_validator.url') . '/validate-items', $payload);

        if (!$response->successful()) {
            $order->update([
                'status' => 'failed',
                'validation_reason' => 'Validation service returned an unexpected response',
            ]);

            return;
        }

        $result = $response->json();

        if (($result['valid'] ?? false) === true) {
            $order->update([
                'status' => 'approved',
                'validation_reason' => null,
            ]);

            return;
        }

        $errors = collect($result['errors'] ?? [])
            ->pluck('reason')
            ->filter()
            ->implode(' | ');

        $order->update([
            'status' => 'rejected',
            'validation_reason' => $errors !== '' ? $errors : 'Validation failed',
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