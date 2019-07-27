<?php

namespace App\Cart\Payments\Gateways;

use App\Models\PaymentMethod;
use App\Cart\Payments\Gateway;
use Stripe\Charge as StripeCharge;
use App\Cart\Payments\GatewayCustomer;
use Stripe\Customer as StripeCustomer;

class StripeGatewayCustomer implements GatewayCustomer
{

    protected $gateway;
    protected $customer;

    public function __construct(Gateway $gateway, StripeCustomer $customer)
    {
        $this->gateway = $gateway;
        $this->customer = $customer;
    }

    public function charge(PaymentMethod $cart, $amount)
    {
        StripeCharge::create([
            'currency' => 'gbp',
            'amount' => $amount,
            'customer' => $this->customer->id,
            'source' => $cart->provider_id
        ]);
    }

    public function addCart($token)
    {
        $cart = $this->customer->sources->create([
            'source' => $token,
        ]);
        $this->customer->default_source = $cart->id;

        $this->customer->save();

        return $this->gateway->user()->paymentMethods()->create([
            'cart_type' => $cart->brand,
            'last_four' => $cart->last4,
            'provider_id' => $cart->id,
            'default' => true
        ]);
    }

    public function id()
    {
        return $this->customer->id;
    }
}
