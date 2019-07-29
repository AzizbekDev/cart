<?php

namespace Tests\Unit\Listeners;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Events\Order\OrderPaymentFaild;
use Illuminate\Foundation\Testing\WithFaker;
use App\Listeners\Order\MarkOrderPaymentFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarkOrderPaymentFailedListenerTest extends TestCase
{

    public function test_marks_order_as_payment_failed()
    {
        $event = new OrderPaymentFaild(
            $order = factory(Order::class)->create([
                'user_id' => factory(User::class)->create()
            ])
        );

        $listener = new MarkOrderPaymentFailed();

        $listener->handle($event);

        $this->assertEquals($order->fresh()->status, Order::PAYMENT_FAILED);
    }
}
