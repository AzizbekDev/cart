<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Order;
use App\Models\Address;
use Faker\Generator as Faker;
use App\Models\ShippingMethod;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'address_id' => factory(Address::class)->create()->id,
        'shipping_method_id' => factory(ShippingMethod::class)->create()->id,
    ];
});
