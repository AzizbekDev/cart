<?php

namespace Tests\Unit\Porducts;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    public function test_is_uses_the_slug_for_the_route_key_name()
    {
        $product = new Product();
        $this->assertEquals($produc->getRouteKeyName(), 'slug');


    }
}
