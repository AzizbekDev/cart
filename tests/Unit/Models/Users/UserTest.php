<?php

namespace Tests\Unit\Models\Users;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    public function test_it_hashes_the_password_when_creating()
    {
        $user = factory(User::class)->create([
            'password' => 'test'
        ]);
        $this->assertNotEquals($user->password, 'test');
    }

    public function test_it_has_many_products()
    {
        $user = factory(User::class)->create();
        $user->cart()->attach(
            factory(ProductVariation::class)->create()
        );
        $this->assertInstanceOf(ProductVariation::class, $user->cart->first());
    }


    public function test_it_has_quantity_for_each_product()
    {
        $user = factory(User::class)->create();
        $user->cart()->attach(
            factory(ProductVariation::class)->create(),
            [
                'quantity' => $quantity = 5
            ]
        );
        return $this->assertEquals($user->cart->first()->pivot->quantity, $quantity);
    }

    public function test_it_has_many_addresses()
    {
        $user = factory(User::class)->create();
        $user->addresses()->save(
            factory(Address::class)->make()
        );
        $this->assertInstanceOf(Address::class, $user->addresses->first());
    }

    public function test_it_has_many_orders()
    {
        $user = factory(User::class)->create();

        factory(Order::class)->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(Order::class, $user->orders->first());
    }
}
