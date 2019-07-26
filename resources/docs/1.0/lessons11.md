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

<a name="section-8"></a>

## Episode-109 Showing and switching payment methods `Front-end`

`1` - Create new folder `PaymentMethods` into `resources/js/components/checkout`

`2` - Create new file `PaymentMathods.vue` into `resources/js/components/checkout/paymentMethods`

`3` - Edit `resources/js/components/checkout/paymentMethods/PaymentMethods.vue`

```html
<template>
  <article class="pl-4">
    <h3 class="text-muted pt-2 mb-3">Payment methods</h3>
    <template v-if="selecting">
      <PaymentMethodSelector
        :payment-methods="paymentMethods"
        :selected-payment-method="selectedPaymentMethod"
        @click="paymentMethodSelected"
      />
    </template>
    <template v-else-if="creating">Create new Payment method</template>
    <template v-else>
      <template v-if="selectedPaymentMethod">
        <p>{ selectedPaymentMethod.cart_type } ending { selectedPaymentMethod.last_four }</p>
        <br />
      </template>
      <template v-else>
        <p>No payment method selected</p>
        <br />
      </template>
      <div class="field">
        <p>
          <button
            type="button"
            @click.prevent="selecting = true"
            class="btn btn-primary btn-sm"
          >Change payment method</button>
          <button
            type="button"
            @click.prevent="creating = true"
            class="btn btn-primary btn-sm"
          >Add a payment method</button>
        </p>
      </div>
    </template>
  </article>
</template>
```

js part

```js
<script>
import PaymentMethodSelector from "../paymentMethods/PaymentMethodSelector";
export default {
  props: {
    paymentMethods: {
      required: true,
      type: Array
    }
  },
  data() {
    return {
      selecting: false,
      creating: false,
      selectedPaymentMethod: null
    };
  },
  components: {
    PaymentMethodSelector
  },
  watch: {
    defaultPaymentMethod(v) {
      if (v) {
        this.switchPaymentMethod(v);
      }
    },
    selectedPaymentMethod(paymentMethod) {
      this.$emit("input", paymentMethod.id);
    }
  },
  computed: {
    defaultPaymentMethod() {
      return this.paymentMethods.find(a => a.default === 1);
    }
  },
  methods: {
    paymentMethodSelected(paymentMethod) {
      this.switchPaymentMethod(paymentMethod);
      this.selecting = false;
    },
    switchPaymentMethod(paymentMethod) {
      this.selectedPaymentMethod = paymentMethod;
    },
    created(paymentMethod) {
      this.paymentMethodSelected = paymentMethod;
      this.creating = false;
      this.switchPaymentMethod(paymentMethod);
    }
  }
};
</script>
<style scoped>
article {
  border-left: 1px solid gray;
}
.field {
  display: block;
}
</style>
```

`4` - Create new file `PaymentMethodSelector.vue` into `resources/js/components/checkout/paymentMethods`

`5` - Edit `resources/js/components/checkout/paymentMethods/PaymentMethodSelector.vue`

```html
<template>
  <table class="table table-hover">
    <tbody>
      <tr
        v-for="paymentMethod in paymentMethods"
        :key="paymentMethod.id"
        :class="{'table-success': paymentMethod.id == selectedPaymentMethod.id }"
      >
        <td>
          <p>{ paymentMethod.cart_type } ending ...{ paymentMethod.last_four }</p>
        </td>
        <td>
          <a
            href
            class="btn btn-primary btn-sm"
            role="button"
            @click.prevent="$emit('click', paymentMethod)"
          >Pay with this</a>
        </td>
      </tr>
    </tbody>
  </table>
</template>
```

js part

```js
<script>
export default {
  props: {
    paymentMethods: {
      required: true,
      type: Array
    },
    selectedPaymentMethod: {
      required: true,
      type: Object
    }
  },
  data() {
    return {
      selectedAddressid: 1
    };
  }
};
</script>
```

`6` - Edit `resources/js/pages/checkout/index.vue`

```html
...
 <PaymentMethods :payment-methods="paymentMethods" v-model="form.payment_method_id"></PaymentMethods>
...
```

js part changes

```js
<script>
import PaymentMethods from "../../components/checkout/paymentMethods/PaymentMethods";
...
export default {
  data() {
    return {
      submitting: false,
      address: [],
      paymentMethods: [],
      shippingMethods: [],
      form: {
        address_id: null,
        payment_method_id: null
      }
    };
  },
  ...
  components: {
    CartOverView,
    ShippingAddress,
    PaymentMethods
  },
  methods: {
    ...
    async getPaymentMethods() {
      const auth = {
        headers: { Authorization: "Bearer " + localStorage.getItem("token") }
      };
      let response = await axios.get("api/payment-methods", auth);
      this.paymentMethods = response.data.data;
    },
    ...
  },
  ...
  created() {
    ...
    this.getPaymentMethods();
  }
...
</script>
```

<a name="section-9"></a>

## Episode-110 Attaching payment methods to order

`1` - Create new migration file `add_payment_method_id_to_orders_table`

```command
php artisan make:migration add_payment_method_id_to_orders_table --table=orders
```

`2` - Edit `database/migrations/add_payment_method_id_to_orders_table.php`

```php
...
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('payment_method_id')->unsigned()->index();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method_id');
        });
    }
...
```

`3` - Edit `app/Http/Controllers/Orders/OrderController.php`

- added `payment_method_id` to request

```php
...
protected function createOrder(Request $request, Cart $cart)
    {
        return $request->user()->orders()->create(
            array_merge($request->only(['address_id', 'shipping_method_id', 'payment_method_id']), [
                'subtotal' => $cart->subtotal()->amount()
            ])
        );
    }
...
```

`4` - Edit `app\Models\Order.php`

```php
...
protected $fillable = [
      ...
      'payment_method_id'
    ];
...
```

`5` - Edit `app/Http/Requests/Orders/OrderStoreRequest.php`

```php
 return [
      ...
        'payment_method_id' => [
            'required',
            Rule::exists('payment_methods', 'id')->where(function ($builder) {
                $builder->where('user_id', $this->user()->id);
            })
        ],
        ...
    ];
```

`6` - Edit `tests/Feature/Orders/OrderStoreTest.php`

```php
use App\Models\PaymentMethod;
...
 public function test_it_requires_a_payment_method()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        $this->jsonAs($user, 'POST', 'api/orders')
            ->assertJsonValidationErrors(['payment_method_id']);
    }

    public function test_it_requires_a_payment_method_that_belongs_to_the_authenticated_user()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        $payment = factory(PaymentMethod::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $this->jsonAs($user, "POST", 'api/orders', [
            'payment_method_id' => $payment->id
        ])
            ->assertJsonValidationErrors(['payment_method_id']);
    }
    public function test_it_can_create_an_order()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        list($address, $shipping, $payment) = $this->orderDependencies($user);

        $this->jsonAs($user, "POST", 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ]);
    }

    public function test_it_attaches_the_products_to_the_order()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        list($address, $shipping, $payment) = $this->orderDependencies($user);

        $response = $this->jsonAs($user, "POST", 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ]);

        $this->assertDatabaseHas('product_variation_order', [
            'product_variation_id' => $product->id,
            'order_id' => json_decode($response->getContent())->data->id
        ]);
    }

    public function test_it_fails_to_create_order_if_cart_is_empty()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync([
            ($product = $this->productWithStock())->id => [
                'quantity' => 0
            ]
        ]);

        list($address, $shipping, $payment) = $this->orderDependencies($user);

        $response = $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ])->assertStatus(400);
    }

    public function test_it_fires_an_order_created_event()
    {
        Event::fake();

        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        list($address, $shipping, $payment) = $this->orderDependencies($user);

        $response = $this->jsonAs($user, "POST", 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ]);

        Event::assertDispatched(OrderCreated::class, function ($event) use ($response) {
            return $event->order->id === json_decode($response->getContent())->data->id;
        });
    }

    public function test_it_empties_the_cart_then_ordering()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        list($address, $shipping, $payment) = $this->orderDependencies($user);

        $response = $this->jsonAs($user, "POST", 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ]);

        $this->assertEmpty($user->cart);
    }
    ...
    protected function orderDependencies(User $user)
    {
        $address = factory(Address::class)->create([
            'user_id' => $user->id
        ]);

        $shipping = factory(ShippingMethod::class)->create();
        $shipping->countries()->attach($address->country);

        $payment = factory(PaymentMethod::class)->create([
            'user_id' => $user->id
        ]);

        return [$address, $shipping, $payment];
    }
```

`7` - Edit `database/factories/OrderFactory.php`

```php
return [
    ...
    'payment_method_id' => factory(PaymentMethod::class)->create([
        'user_id' => factory(User::class)->create()->id
    ])->id,
    ...
];
```

<a name="section-10"></a>

## Episode-111 Setting up Stripe

`1` - Installing `Stripe PHP bindings`

```command
composer require stripe/stripe-php
```

`2` - Edit `config/services.php`

```php
...
'stripe' => [
        'secret' => env('STRIPE_SECRET'),
    ],
...
```

`3` - Edit `.env`

- Get stripe Secret key here from => [STRIPE_SECRET](https://dashboard.stripe.com/test/apikeys)

```php
...
STRIPE_SECRET=sk_test_zqELAKG23359rZDSDX34O1KIOSAD6wX435h8SZXCHj453GhS00Duz23ASDI
```

`4`- Edit `app/Providers/AppServiceProvider.php`

- Registering Stripe Service to boot method

```php
use Stripe\Stripe;
...
public function boot()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
      ...
    }
...
```
