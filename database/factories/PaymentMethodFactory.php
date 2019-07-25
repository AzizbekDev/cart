<?php

use App\Models\PaymentMethod;
use Faker\Generator as Faker;

$factory->define(PaymentMethod::class, function (Faker $faker) {
    return [
        'cart_type' => 'Visa',
        'last_four' => '8434',
        'provider_id' => str_random(10)
    ];
});
