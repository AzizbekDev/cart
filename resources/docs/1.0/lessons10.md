# Episodes from 92 to 101

- [92-Returning order details](#section-1)
- [93-Fixing up failing order test](#section-2)
- [94-Placeing orders from the checkout](#section-3)
- [95-Warning users of cart changes, plus some refactoring](#section-4)
- [96-Alerting on checkout changes](#section-5)
- [97-Fixing the quantity UI bug](#section-6)
- [98-Orders endpoint](#section-7)
- [99-Formatting order total and subtotal](#section-8)
- [100-Order index setup](#section-9)
- [101-Listing through orders](#section-10)

<a name="section-1"></a>

## Episode-92 Returning order details

`1` - Create new Resource file `OrderResource`

```command
php artisan make:resouce OrderResource
```

`2` - Edit `app/Http/Resources/OrderResource.php`

```php
...
public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'subtotal' => $this->subtotal,
            'products' => ProductVariationResource::collection(
                $this->whenLoaded('products')
            ),
            'address' => new AddressResource(
                $this->whenLoaded('address')
            ),
            'shippingMethod' => new ShippingMethodResource(
                $this->whenLoaded('shippingMethod')
            )
    }
...
```

`3` - Edit `app/Http/Controllers/Orders/OrderController.php`

```php
use App\Http\Resources\OrderResource;
...
public function store(OrderStoreRequest $request, Cart $cart)
    {
        ...
        return new OrderResource($order);
    }
...
```

`4` - Edit `app/Events/Order/OrderCreated.php`

```php
<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class OrderCreated
{
    use Dispatchable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}

```

`5` - Edit `tests/Feature/Orders/OrderStoreTest.php`

```php
...
     public function test_it_attaches_the_products_to_the_order()
    {
       ...
        $this->assertDatabaseHas('product_variation_order', [
            'product_variation_id' => $product->id,
            'order_id' => json_decode($response->getContent())->data->id
        ]);
    }

    ...
        public function test_it_fires_an_order_created_event()
    {
        ...

        Event::assertDispatched(OrderCreated::class, function ($event) use ($response) {
            return $event->order->id === json_decode($response->getContent())->data->id;
        });
    }
...
```
<a name="section-2"></a>

## Episode-93 Fixing up failing order test

`1` - Edit `tests/Feature/Orders/OrderStoreTest.php`

```php
...
  public function test_it_can_create_an_order()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );
        ...
    }
...
```

<a name="section-3"></a>

## Episode-94 Placeing orders from the checkout  `Front-end`

`1` - Edit `resources/js/pages/checkout/index.vue`

```php
<template>
...
<button
    class="btn btn-primary btn-sm btn-block"
    type="submit"
    :disabled="empty || submitting"
    @click.prevent="order"
>
...
</template>
```

JS part changes 

```js
...
  data() {
    return {
      submitting: false,
      ...
    };
  },
...
methods: {
...
async order() {
    this.submitting = true;
    try {
    axios.post("api/orders", {
        ...this.form,
        shipping_method_id: this.shippingMethodId
    });
    await this.getCart();
    this.$router.replace({
        name: "orders"
    });
    } catch (e) {
    //
    }
},
...
}
```

`2` - Edit `resources/js/router/routes.js`

```js
import Orders from '../pages/orders'
...
export default [
    ...
    {
        path: '/orders',
        name: 'orders',
        component: Orders,
        meta: {
            guest: false,
            needsAuth: true
        }
    },
    ...
];
```

`3` - Create new Folder `orders`

`4` - Create new file `index.vue` in to `resources/js/pages/orders/index.vue`

`5` - Edit `resources/js/pages/orders/index.vue`

```html
<template>
  <p>Orders page</p>
</template>
<script>
export default {
  //
};
</script>
```

`6` - Edit `resources/js/components/Navbar.vue`

```html
<router-link class="btn btn-light" to="/order" exact>Order</router-link>
```

change route name to `orders`

```html
<router-link class="btn btn-light" to="/orders" exact>Order</router-link>
```

<a name="section-4"></a>

## Episode-95 Warning users of cart changes, plus some refactoring

`1` - Create new Middleware `Sync`

```command
php artisan make:middleware Cart\\Sync
```
`2` - Edit `app/Http/Middleware/Cart/Sync.php`

```php
<?php

namespace App\Http\Middleware\Cart;

use Closure;
use App\Cart\Cart;

class Sync
{
    protected $cart;
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function handle($request, Closure $next)
    {
        $this->cart->sync();
        if ($this->cart->hasChanged()) {
            return response()->json([
                'message' => 'Oh no, some items in your cart have changed. Pleace review these changes before placing your order.'
            ], 409);
        }
        return $next($request);
    }
}
```

`3` - Edit `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
...
'cart.sync' => \App\Http\Middleware\Cart\Sync::class,
...
```

`4` - Edit `app/Http/Controllers/Orders/OrderController.php`

- using `cart.sync` middleware in a __construct mehtod

```php
...
public function __construct()
    {
        $this->middleware(['auth:api', 'cart.sync']);
    }
...
```

`5` - Create new middleware `ResponseIfEmpty`

```command
php artisan make:middleware Cart\\ResponseIfEmpty
```

`6` - Edit `app/Http/Middleware/Cart/ResponseIfEmpty.php`

```php
<?php

namespace App\Http\Middleware\Cart;

use Closure;
use App\Cart\Cart;

class ResponseIfEmpty
{
    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function handle($request, Closure $next)
    {
        if ($this->cart->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 400);
        }
        return $next($request);
    }
}
```

`7` - Edit `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
...
'cart.isnotempty' => \App\Http\Middleware\Cart\ResponseIfEmpty::class,
...
```

`8` - Edit `app/Http/Controllers/Orders/OrderController.php`

- using also `cart.isnotempty` middleware in a __construct mehtod

```php
  public function __construct()
    {
        $this->middleware(['auth:api', 'cart.sync', 'cart.isnotempty']);
    }

    public function store(OrderStoreRequest $request, Cart $cart)
    {
        $order = $this->createOrder($request, $cart);

        $order->products()->sync($cart->products()->forSyncing());

        event(new OrderCreated($order));

        return new OrderResource($order);
    }
...
```

`9` - Edit `app/Providers/AppServiceProvider.php`

- Fixing after setup new middleware occurse some errors

```php
...
    public function register()
    {
        $this->app->singleton(Cart::class, function ($app) {
            if ($app->auth->user()) {
                $app->auth->user()->load([
                    'cart.stock',
                ]);
            }
            return new Cart($app->auth->user());
        });
    }
...
```

`10` - Edit `app/Cart/Cart.php`

```php
...
    public function __construct(User $user)
    {
        $this->user = $user;
    }
...
```

Change to

```php
...
    public function __construct($user)
    {
        $this->user = $user;
    }
...
```

`11` - Edit  `tests/Feature/Orders/OrderStoreTest.php`

- Fixing after setup new middleware occurse some errors

```php
...
    public function test_it_requires_an_address()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );
        ...
    }
    public function test_it_requires_an_address_that_exists()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );
        ...
    }
    public function test_it_requires_an_address_that_belongs_to_user()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );
        ...
    }
    public function test_it_requires_a_shipping_method()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );
        ...
    }
     public function test_it_requires_a_shipping_method_that_exists()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );
        ...
    }
    public function test_it_requires_a_shipping_method_valid_for_given_address()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );
        ...
    }
...
```

<a name="section-5"></a>

## Episode-96 Alerting on checkout changes

`1` - Create new folder `globals` in to `resources/js/components`

`2` - Create new file `TheAlert-vue` in to `resources/js/components/globals`

`3` - Edit `resources/js/components/globals/TheAlert.vue`

```html
<template>
  <div
    class="alert alert-info alert-dismissible fade show"
    role="alert"
    v-if="alert"
    @click.prevent="clearMessage"
  >
    { alert }.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
export default {
  computed: {
    ...mapGetters({
      alert: "alertMessage"
    })
  },
  methods: {
    ...mapActions({
      clearMessage: "clearMessage"
    })
  }
};
</script>
```

`4` - Edit `resources/js/App.vue`

```html
...
<Alert></Alert>
...
<script>
import Alert from "./components/globals/TheAlert";
...
export default {
  components: {
    Alert,
    ...
  }
};
...
</style>
```

`5` - Edit `resources/js/store/state.js`

```js
export default {
...
 alert: {
        message: null
    }
...
```

`6` - Edit `resources/js/store/actions.js`

```js
...
export const flash = ({
    commit
}, message) => {
    commit('setMessage', message)
}

export const clearMessage = ({
    commit
}) => {
    commit('setMessage', null)
}
```

`7` - Edit `resources/js/store/getters.js`

```js
...
export const alertMessage = (state) => {
    return state.alert.message
}
```

`8` - Edit `resources/js/store/mutations.js`

```js
...
export const setMessage = (state, message) => {
    state.alert.message = message
}
```

`9` - Edit `resources/js/pages/checkout/index.vue`

```js
...
 methods: {
    ...mapActions({
      setShipping: "storeShipping",
      getCart: "getCart",
      flash: "flash"
    }),
    ...
    async order() {
        try {
        ...
        } catch (e) {
        this.flash(e.response.data.message);
        this.getCart();
      }
      this.submitting = false;
    }
 },
...
```

<a name="section-6"></a>

## Episode-97 Fixing the quantity UI bug

`1` - Edit `resources/js/components/cart/CartOverviewProduct.vue`

```js
export default {
...
  data() {
    return {
      quantity: this.product.quantity
    };
  },
  watch: {
    quantity(quantity) {
      this.update({ productId: this.product.id, quantity });
    }
  },
...
}
```

change to

```js
export default {
  computed: {
    quantity: {
      get() {
        return this.product.quantity;
      },
      set(quantity) {
        this.update({ productId: this.product.id, quantity });
      }
    }
  },
  ...
}
```

`2` - Edit `resources/js/pages/checkout/index.vue`

- added `await` syntax

```js
methods: {
...
async order() {
      this.submitting = true;
      try {
        await axios.post("api/orders", {
        ...
        });
        ...
      } catch (e) {
        ...
    }
    ...
},
...
```

<a name="section-7"></a>

## Episode-98 Orders endpoint

`1` - Edit `app/Http/Controllers/Orders/OrderController.php`

```php
...
    public function __construct()
    {
        $this->middleware(['auth:api']);
        $this->middleware(['cart.sync', 'cart.isnotempty'])->only('store');
    }

    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['products', 'address', 'shippingMethod'])
            ->latest()
            ->paginate();
        return OrderResource::collection($orders);
    }
...
```

`2` - Create new test `OrderIndexTest`

```command
php artisan make:test Orders\\OrderIndexTest
```

`3` - Edit `tests/Feature/Orders/OrderIndexTest.php`

```php
<?php

namespace Tests\Feature\Orders;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;

class OrderIndexTest extends TestCase
{
    public function test_it_fails_if_user_isnt_authenticated()
    {
        $this->json('GET', 'api/orders')
            ->assertStatus(401);
    }

    public function test_it_returns_a_collection_of_orders()
    {
        $user = factory(User::class)->create();

        $order = factory(Order::class)->create([
            'user_id' => $user->id
        ]);

        $this->jsonAs($user, 'GET', 'api/orders')
            ->assertJsonFragment([
                'id' => $order->id
            ]);
    }

    public function test_it_orders_by_the_latest_first()
    {
        $user = factory(User::class)->create();

        $order = factory(Order::class)->create([
            'user_id' => $user->id
        ]);

        $anotherOrder = factory(Order::class)->create([
            'user_id' => $user->id,
            'created_at' => now()->subDay()
        ]);

        $this->jsonAs($user, 'GET', 'api/orders')
            ->assertSeeInOrder([
                $order->created_at->toDateTimeString(),
                $anotherOrder->created_at->toDateTimeString()
            ]);
    }

    public function test_it_has_a_pagination()
    {
        $user = factory(User::class)->create();

        $this->jsonAs($user, 'GET', 'api/orders')
            ->assertJsonStructure([
                'links',
                'meta'
            ]);
    }
}
```

<a name="section-8"></a>

## Episode-99 Formatting order total and subtotal

`1` - Edit `app/Models/Order.php`

```php
use App\Cart\Money;
...
 public function getSubtotalAttribute($subtotal)
    {
        return new Money($subtotal);
    }

    public function total()
    {
        return $this->subtotal->add($this->shippingMethod->price);
    }
...
```

`2` - Edit `app/Http/Resources/OrderResource.php`

```php
    public function toArray($request)
    {
        return [
            ...
            'subtotal' => $this->subtotal->formatted(),
            'total' => $this->total()->formatted(),
            ...
        ];
    }
```

`3` - Edit `tests/Unit/Models/Orders/OrderTest.php`

```php
use App\Cart\Money;
...
    public function test_it_returns_a_money_instance_for_the_subtotal()
    {
        $order = factory(Order::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $this->assertInstanceOf(Money::class, $order->subtotal);
    }

    public function test_it_returns_a_money_instance_for_the_total()
    {
        $order = factory(Order::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $this->assertInstanceOf(Money::class, $order->total());
    }

    public function test_it_adds_shipping_onto_the_total()
    {
        $order = factory(Order::class)->create([
            'user_id' => factory(User::class)->create()->id,
            'subtotal' => 1000,
            'shipping_method_id' => factory(ShippingMethod::class)->create([
                'price' => 1000
            ])
        ]);

        $this->assertEquals($order->total()->amount(), 2000);
    }
...
```

<a name="section-9"></a>

## Episode-100 Order index setup  `Front-end`

`1` - Edit `resources/js/pages/orders/index.vue`

```html
<template>
  <section id="order-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h3 class="text-muted py-2">Your order</h3>
          <article class="py-3 px-3">
            <table class="table table-hover">
              <tbody>
                <Order />
              </tbody>
            </table>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
import Order from "../../components/orders/Order";
export default {
  components: {
    Order
  }
};
</script>
<style scoped>
article {
  background-color: #cccccc0f;
  border-left: 1px solid gray;
}
</style>
```

`2` - Create new folder `orders` in to `resources/js/components`

`3` - Create new file `Order.vue` in to `resources/js/components/orders`

`4` - Edit  `resources/js/components/orders/Order.vue`

```html
<template>
  <tr>
    <th scope="col">#1</th>
    <th scope="col">2018-01-01</th>
    <th scope="col">
      <div>
        <a href="#">Product 1</a>
        <a href="#">Product 2</a>
      </div>
      <template>and more</template>
    </th>
    <th scope="col">$30.00</th>
    <th scope="col">
      <span class="text-danger">Pending</span>
    </th>
  </tr>
</template>
<script>
export default {
  //
};
</script>
<style scoped>
.table th,
.table td {
  border-top: none;
}
</style>
```
