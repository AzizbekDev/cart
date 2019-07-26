<?php

namespace App\Cart\Payments\Gateways;

use App\Models\PaymentMethod;
use App\Cart\Payments\GatewayCustomer;

class StripeGatewayCustomer implements GatewayCustomer
{
    public function charge(PaymentMethod $cart, $amount)
    {
        // 
    }

    public function addCard($token)
    {
        dd('add cart');
    }
}
