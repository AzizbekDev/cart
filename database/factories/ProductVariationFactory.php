<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Product;
use Faker\Generator as Faker;
use App\Models\ProductVariation;

$factory->define(ProductVariation::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'product_id' => factory(Product::class)->create()->id
    ];
});
