<?php

namespace App\Console\Commands;

use App\Services\DeliverySlaService;
use Illuminate\Console\Command;

class EnforceDeliverySla extends Command
{
    protected $signature = 'orders:enforce-delivery-sla';

    protected $description = 'Notify customers when delivery orders breach their SLA deadline';

    public function handle(DeliverySlaService $deliverySlaService): int
    {
        $sent = $deliverySlaService->notifyBreachedOrders();

        $this->info("Delivery SLA breach notifications sent: {$sent}");

        return self::SUCCESS;
    }
}
