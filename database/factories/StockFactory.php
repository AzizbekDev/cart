<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Stock;
use Faker\Generator as Faker;

$factory->define(Stock::class, function (Faker $faker) {
    return [
        'quantity' => 1,
        // 'product_variation_id' => factory(ProductVariation::class)->create()->id
    ];
});
