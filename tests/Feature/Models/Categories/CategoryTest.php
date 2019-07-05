<?php

namespace Tests\Feature\Models\Categories;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    /**
     * @test
     */
    public function test_is_has_many_children()
    {
        $category = factory(Category::class)->create();
        $category->children()->save(
            factory(Category::class)->create()
        );
        $this->assertInstanceOf(Category::class, $category->children->first());
    }

     /**
     * @test
     */
    public function test_it_can_fetch_only_parents()
    {
        $category = factory(Category::class)->create();
        $category->children()->save(
            factory(Category::class)->create()
        );
        $this->assertEquals(1, $category->parents()->count());
    }

    /**
     * @test
     */
    public function test_it_is_orderable_by_a_numbered_order()
    {
        $category = factory(Category::class)->create([
            'order' => 2
        ]);
        $categorySecond = factory(Category::class)->create([
            'order' => 1
        ]);
        
        $this->assertEquals($categorySecond->name, Category::ordered()->first()->name);
    }

    /**
     * @test
     */
    public function test_it_has_many_products()
    {
        $category = factory(Category::class)->create();

        $category->products()->save(
            factory(Product::class)->create()
        );

        $this->assertInstanceOf(Product::class, $category->products->first())
    }
}
