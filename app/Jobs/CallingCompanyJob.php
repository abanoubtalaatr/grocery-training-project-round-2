<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class CallingCompanyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(): void
    {
        Log::info('CallingCompanyJob started for order ' . $this->orderId);

        $order = Order::with(['user', 'address'])->find($this->orderId);

        if (! $order) {
            Log::warning('Order not found for CallingCompanyJob: ' . $this->orderId);
            return;
        }

        try {
            // Example: perform an HTTP POST to an external service
            $endpoint = config('services.company.endpoint');
            if ($endpoint) {
                Http::post($endpoint, [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->user?->email,
                    'address' => $order->address?->full_address,
                ]);
                Log::info('CallingCompanyJob posted to ' . $endpoint . ' for order ' . $this->orderId);
            } else {
                Log::info('No company endpoint configured; skipping external call for order ' . $this->orderId);
            }
        } catch (\Exception $e) {
            Log::error('CallingCompanyJob error: ' . $e->getMessage());
            throw $e;
        }
    }
}
