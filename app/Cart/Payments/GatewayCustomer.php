<?php

namespace App\Cart\Payments;

use App\Models\PaymentMethod;

interface GatewayCustomer
{
    public function charge(PaymentMethod $cart, $amount);

    public function addCart($token);

    public function id();
}