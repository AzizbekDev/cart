# Episodes from 102 to 111

- [102-Product variation product relationship](#section-1)
- [103-Updating product variations in orders](#section-2)
- [104-Refactoring statuses to dinamic components](#section-3)
- [105-Fixing syncing bug](#section-4)
- [106-Setting up payment methods](#section-5)
- [107-Refactoring defaults to a trait](#section-6)
- [108-Payment method index endpoint](#section-7)
- [109-Showing and switching payment methods](#section-8)
- [110-Attaching payment methods to orders](#section-9)
- [111-Setting up Stripe](#section-10)

<a name="section-1"></a>

## Episode-102 Product variation product relationship

`1` -  Edit `app/Http/Resources/ProductVariationResource.php`

```php
use App\Models\User;
...
return [
        ...
        'product' => new ProductIndexResource($this->product)
    ];
...
```

`2` - Edit  `app/Http/Controllers/Orders/OrderController.php`

- Added Eager Loading relations

```php
public function index(Request $request)
{
    $orders = $request->user()->orders()
        ->with([
            'products',
            'products.stock',
            'products.type',
            'products.product',
            'products.product.variations',
            'products.product.variations.stock'
            'address',
            'shippingMethod'
        ])
        ->latest()
        ->paginate();
    return OrderResource::collection($orders);
}
```

<a name="section-2"></a>

## Episode-103 Updating product variations in orders `Front-end`

`1` -  Edit `resources/js/components/orders/Order.vue`

```html
...
<div v-for="variation in products" :key="variation.id">
        <router-link
          :to="{
          name: 'products-slug',
          params:{
            slug: variation.product.slug
          }
        }"
        >{ variation.product.name } ({ variation.name }) - { variation.type }</router-link>
...
```

<a name="section-3"></a>

## Episode-104 Refactoring statuses to dinamic components

`1` - Edit  `resources/js/components/orders/Order.vue`

```html
...
<th scope="col">
 <span :class="statusClass">{ order.status }</span>
</th>
...
```

change to

```html
...
<th scope="col">
  <component :is="order.status" />
</th>
...
```

js part changes

```js
<script>
import OrderStatusPaymentFailed from "./statuses/OrderStatus-payment_failed";
import OrderStatusPending from "./statuses/OrderStatus-pending";
export default {
  components: {
    payment_failed: OrderStatusPaymentFailed,
    pending: OrderStatusPending
  },
  data() {
    return {
      maxProducts: 2
    };
  },
...
}
```

`2` - Create new folder `statuses` into `resources/js/components/orders`

`3` - Create new file `OrderStatus-pending.vue` into `resources/js/components/orders/statuses`

`4` - Create new file `OrderStatus-payment_failed.vue` into `resources/js/components/orders/statuses`

`5` - Edit `resources/js/components/orders/statuses/OrderStatus-payment_failed.vue`

```html
<template>
  <div class="text-danger">Payment failed</div>
</template>
```

`6` - Edit `resources/js/components/orders/statuses/OrderStatus-pending.vue`

```html
<template>
  <div class="text-info">Pending</div>
</template>
```

<a name="section-4"></a>

## Episode-105 Fixing syncing bug

`1` - Edit  `tests/Unit/Cart/CartTest.php`

```php
...
 public function test_it_syncs_the_cart_to_update_the_quantites()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $product = factory(ProductVariation::class)->create();

        $anotherProduct = factory(ProductVariation::class)->create();

        $user->cart()->attach([
            $product->id => [
                'quantity' => 2
            ],
            $anotherProduct->id => [
                'quantity' => 2
            ]
        ]);

        $cart->sync();

        $this->assertEquals($user->fresh()->cart->first()->pivot->quantity, 0);
        $this->assertEquals($user->fresh()->cart->get(1)->pivot->quantity, 0);
    }

    public function test_it_can_check_if_the_cart_has_changed_after_syncing()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $product = factory(ProductVariation::class)->create();

        $anotherProduct = factory(ProductVariation::class)->create();

        $user->cart()->attach([
            $product->id => [
                'quantity' => 2
            ],
            $anotherProduct->id => [
                'quantity' => 0
            ]
        ]);

        $cart->sync();

        $this->assertTrue($cart->hasChanged());
    }
...
```

`2` - Edit `app/Cart/Cart.php`

```php
...
public function sync()
    {
        $this->user->cart->each(function ($product) {
            $quantity = $product->minStock($product->pivot->quantity);

            if ($quantity != $product->pivot->quantity) {
                $this->changed = true;
            }

            $product->pivot->update([
                'quantity' => $quantity
            ]);
        });
    }
...
```

<a name="section-5"></a>

## Episode-106 Setting up payment methods

`1` - Create new model `PaymentMethod` with migrations

```command
php artisan make:model Models\\PaymentMethod -m
```

`2` - Edit ``

```php
...
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->index();
            $table->string('cart_type')->nullable();
            $table->string('last_four')->nullable();
            $table->boolean('default')->default(true);
            $table->string('provider_id')->unique();
            $table->timestamps();
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
...
```

`3` - Edit `app\Models\User.php`

```php
use App\Models\PaymentMethod;
...
public function paymentMethods()
{
    return $this->hasMany(PaymentMethod::class);
}
...
```

`4` - Edit `app/Models/PaymentMethod.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\ProductIndexResource;

class PaymentMethod extends Model
{
    protected $fillable = [
        'cart_type',
        'last_four',
        'provider_id',
        'default'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($paymentMethod) {
            if ($paymentMethod->default) {
                $paymentMethod->user->paymentMethods()->update([
                    'default' => false
                ]);
            }
        });
    }

    public function setDefaultAttribute($value)
    {
        $this->attributes['default'] = ($value === 'true' || $value ? true : false);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

`5` - Create new factory `PaymentMethodFactory`

```command
php artisan make:factory PaymentMethodFactory
```

`6` - Edit `database/factories/PaymentMethodFactory.php`

```php
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
```

`7` - Edit `tests/Unit/Models/Users/UserTest.php`

```php
use App\Models\PaymentMethod;
...
public function test_it_has_many_payment_methods()
    {
        $user = factory(User::class)->create();

        factory(PaymentMethod::class)->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(PaymentMethod::class, $user->paymentMethods->first());
    }
...
```

`8` - Create new test file `PaymentMethodTest` this will be unit test

```command
php artisan make:test Models\\PaymentMethods\\PaymentMethodTest --unit
```

`9` - Edit `tests/Unit/Models/PaymentMethods/PaymentMethodTest.php`

```php
<?php

namespace Tests\Unit\Models\PaymentMethods;

use Tests\TestCase;
use App\Models\User;
use App\Models\PaymentMethod;

class PaymentMethodTest extends TestCase
{
    public function test_it_belongs_to_user()
    {
        $paymentMethod = factory(PaymentMethod::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $this->assertInstanceOf(User::class, $paymentMethod->user);
    }

    public function test_it_sets_old_payment_method_to_not_default_when_creating()
    {
        $user = factory(User::class)->create();

        $oldPaymentMethod = factory(PaymentMethod::class)->create([
            'default' => true,
            'user_id' => $user->id
        ]);

        factory(PaymentMethod::class)->create([
            'default' => true,
            'user_id' => $user->id
        ]);

        $this->assertEquals($oldPaymentMethod->fresh()->default, 0);
    }
}
```

<a name="section-6"></a>

## Episode-107 Refactoring defaults to a trait

`1` - Create new trait file `CanBeDefault` and edit `app/Models/Traits/CanBeDefault.php`

```php
<?php

namespace App\Models\Traits;

trait CanBeDefault
{
    public static function boot()
    {
        parent::boot();
        static::creating(function ($address) {
            if ($address->default) {
                $address->newQuery()
                ->where('user_id', $address->user->id)
                ->update([
                    'default' => false
                ]);
            }
        });
    }

    public function setDefaultAttribute($value)
    {
        $this->attributes['default'] = ($value === 'true' || $value ? true : false);
    }
}
```

`2` - Edit `app/Models/PaymentMethod.php`

```php
<?php

namespace App\Models;

use App\Models\User;
use App\Models\Traits\CanBeDefault;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use CanBeDefault;

    protected $fillable = [
        'cart_type',
        'last_four',
        'provider_id',
        'default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

`3` - Edit `app/Models/Address.php`

```php
<?php

namespace App\Models;

use App\Models\Traits\CanBeDefault;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use CanBeDefault;

    protected $fillable = [
        'name',
        'address_1',
        'city',
        'postal_code',
        'country_id',
        'default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }
}
```

<a name="section-7"></a>

## Episode-108 Payment method index endpoint

`1` - Create new controller file `PaymentMethodController`

```command
php artisan make:controller PaymentMethods\\PaymentMethodController
```

`2` - Edit `routes/api.php`

```php
...
Route::resource('payment-methods', 'PaymentMethods\PaymentMethodController');
...
```

`3` - Edit `app/Http/Controllers/PaymentMethods/PaymentMethodController.php`

```php
<?php

namespace App\Http\Controllers\PaymentMethods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function index(Request $request)
    {
        return PaymentMethodResource::collection($request->user()->paymentMethods);
    }
}
```

`4` - Create new resouce file `PaymentMethodResouce`

```command
php artisan make:resource PaymentMethodResource
```

`5` - Edit `app/Http/Resources/PaymentMethodResource.php`

```php
...
public function toArray($request)
{
    return [
        'id' => $this->id,
        'cart_type' => $this->cart_type,
        'last_four' => $this->last_four,
        'default' => $this->default
    ];
}
...
```

`6` - Create new test file `PaymentMethodIndexTest` this will be feature test

```command
php artisan make:test PaymentMethods\\PaymentMethodIndexTest
```

`7` - Edit  `tests/Feature/PaymentMethods/PaymentMethodIndexTest.php`

```php
<?php

namespace Tests\Feature\PaymentMethods;

use Tests\TestCase;
use App\Models\User;
use App\Models\PaymentMethod;

class PaymentMethodIndexTest extends TestCase
{
    public function test_it_fails_if_not_authenticated()
    {
        $this->json('GET', 'api/payment-methods')
            ->assertStatus(401);
    }

    public function test_it_returns_a_collection_of_payment_methods()
    {
        $user = factory(User::class)->create();


        $payment = factory(PaymentMethod::class)->create([
            'user_id' => $user->id
        ]);

        $this->jsonAs($user, 'GET', 'api/payment-methods')
            ->assertJsonFragment([
                'id' => $payment->id
            ]);
    }
}
```
