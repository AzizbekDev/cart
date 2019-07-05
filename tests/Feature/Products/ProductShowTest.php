<?php

namespace Tests\Feature\Products;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductShowTest extends TestCase
{

    public function test_it_fails_if_a_product_can_be_found()
    {
        $this->json('GET','api/products/nope')->assertStatus(404);
    }

    public function test_it_shows_a_products()
    {
        $product = factory(Product::class)->create();
        
        $this->json('GET',"api/products/{$product->slug}")->assertJsonFragment([
            'id', $product->id
        ]);
    }
}
