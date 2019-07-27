<?php

namespace App\Listeners\Order;

use App\Cart\Payments\Gateway;
use App\Events\Order\OrderCreated;
use App\Events\Order\OrderPaymentFaild;
use Illuminate\Queue\InteractsWithQueue;
use App\Exceptions\PaymentFaildException;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessPayment implements ShouldQueue
{
    protected $gateway;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Handle the event.
     *
     * @param  OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        $order = $event->order;

        try {
            $this->gateway->withUser($order->user)
                ->getCustomer()
                ->charge(
                    $order->paymentMethod,
                    $order->total()->amount()
                );

            //event

        } catch (PaymentFaildException $e) {
            event(new OrderPaymentFaild($order));
        }
    }
}