<?php

namespace App\Listeners\Order;

use App\Models\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkOrderProcessing
{

    public function handle($event)
    {
        $event->order->update([
            'status' => Order::PROCESSING
        ]);
    }
}
