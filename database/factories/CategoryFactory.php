<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {
    return [
            'name' => $name = $faker->unique()->name,
            'slug' => str_slug($name)
    ];
});
