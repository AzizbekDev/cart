<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;
use App\Models\ProductVariationType;

$factory->define(ProductVariationType::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name        
    ];
});
