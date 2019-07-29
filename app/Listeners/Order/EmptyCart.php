<?php

namespace App\Listeners\Order;

use App\Cart\Cart;

class EmptyCart
{
    protected $cart;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * handling method
     *
     * @return void
     */
    public function handle()
    {
        $this->cart->empty();
    }
}