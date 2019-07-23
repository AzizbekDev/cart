# Episodes from 82 to 91

- [82-Order statuses and defaults](#section-1)
- [83-Basic order validation](#section-2)
- [84-Custom shipping method validation rule](#section-3)
- [85-Creating an order](#section-4)
- [86-Revisiting orders and product relations](#section-5)
- [87-Fixing cart store failing test](#section-6)
- [88-Attaching products when ordering](#section-7)
- [89-Refactoring to a custom collection](#section-8)
- [90-Failing if the cart is empty](#section-9)
- [91-Emptying the cart when ordering](#section-10)

<a name="section-1"></a>

## Episode-82 Order statuses and defaults

`1` - Edit `app/Models/Order.php`

```php
...
class Order extends Model
{
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const PAYMENT_FAILED = 'payment_faild';
    const COMPLETED = 'completed';

    protected $fillable = [
        'status',
        'address_id',
        'shipping_method_id'
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->status = self::PENDING;
        });
    }
    ...
}
```

`2` - Create new migration file `add_status_to_orders_table`

```command
    php artisan make:migration add_status_to_orders_table --table=orders
```

`3` - Edit `database/migrations/2019_07_22_123243_add_status_to_orders_table.php`

```php
...
public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending');
        });
    }
public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
```

`4` - Edit `tests/Unit/Models/Orders/OrderTest.php`

```php
...
    public function test_it_has_a_default_status_of_pending()
    {
        $order = factory(Order::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $this->assertEquals($order->status, Order::PENDING);
    }
...
```

<a name="section-2"></a>

## Episode-83 Basic order validation

`1` - Create new Controller file `OrderController`

```command
php artisan make:controller Orders\\OrderController
```

`2` - Edit ``

```php
<?php

namespace App\Http\Controllers\Orders;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderStoreRequest;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function store(OrderStoreRequest $request)
    {
        dd('test');
    }
}
```

`3` - Edit `routes/api.php`

```php
...
Route::resource('orders', 'Orders\OrderController');
...
```

`4` - Create new request file `OrderSoreRequest`

```command
php artisan make:request Orders\\OrderStoreRequest
```

`5` - Edit `app/Http/Requests/Orders/OrderStoreRequest.php`

```php
<?php
namespace App\Http\Requests\Orders;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'address_id' => [
                'required',
                Rule::exists('addresses', 'id')->where(function ($builder) {
                    $builder->where('user_id', $this->user()->id);
                })
            ],
            'shipping_method_id' => [
                'required',
                'exists:shipping_methods,id'
            ]
        ];
    }
}
```

`6` - Create new Test `OrderStoreTest` this will be feature test

```command
php artisan make:test Orders\\OrderStoreTest
```

`7` - Edit `tests/Feature/Orders/OrderStoreTest.php`

```php
<?php

namespace Tests\Feature\Orders;

use Tests\TestCase;
use App\Models\User;
use App\Models\Address;

class OrderStoreTest extends TestCase
{
    public function test_it_fails_if_not_authenticated()
    {
        $this->json("POST", 'api/orders')
            ->assertStatus(401);
    }

    public function test_it_requires_an_address()
    {
        $user = factory(User::class)->create();

        $this->jsonAs($user, "POST", 'api/orders')
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_it_requires_an_address_that_exists()
    {
        $user = factory(User::class)->create();

        $this->jsonAs($user, "POST", 'api/orders', [
            'address_id' => 1
        ])
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_it_requires_an_address_that_belongs_to_user()
    {
        $user = factory(User::class)->create();

        $address = factory(Address::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $this->jsonAs($user, "POST", 'api/orders', [
            'address_id' => $address->id
        ])->assertJsonValidationErrors(['address_id']);
    }

    public function test_it_requires_a_shipping_method()
    {
        $user = factory(User::class)->create();

        $this->jsonAs($user, "POST", 'api/orders')
            ->assertJsonValidationErrors(['shipping_method_id']);
    }

    public function test_it_requires_a_shipping_method_that_exists()
    {
        $user = factory(User::class)->create();

        $this->jsonAs($user, "POST", 'api/orders', [
            'shipping_method_id' => 1
        ])
            ->assertJsonValidationErrors(['shipping_method_id']);
    }
}
```


<a name="section-3"></a>

## Episode-84 Custom shipping method validation rule

`1` - Creating a new role file `ValidShippingMethod`

```command
php artisan make:rule ValidShippingMethod
```

`2` - Edit `app/Rules/ValidShippingMethod.php`

```php
<?php

namespace App\Rules;

use App\Models\Address;
use Illuminate\Contracts\Validation\Rule;

class ValidShippingMethod implements Rule
{
    protected $addressId;

    public function __construct($addressId)
    {
        $this->addressId = $addressId;
    }

    public function passes($attribute, $value)
    {
        if (!$address = $this->getAddress()) {
            return false;
        }
        return $address->country->shippingMethods->contains('id', $value);
    }

    public function message()
    {
        return 'Invalid shipping method.';
    }

    protected function getAddress()
    {
        return Address::find($this->addressId);
    }
}
```

`3` - Edit `app/Http/Requests/Orders/OrderStoreRequest.php`

```php
public function rules()
{
    return [
        'address_id' => [
            'required',
            Rule::exists('addresses', 'id')->where(function ($builder) {
                $builder->where('user_id', $this->user()->id);
            })
        ],
        'shipping_method_id' => [
            'required',
            'exists:shipping_methods,id',
            new ValidShippingMethod($this->address_id)
        ]
    ];
}
```

`4` - Edit `tests/Feature/Orders/OrderStoreTest.php`

```php
...
public function test_it_requires_a_shipping_method_valid_for_given_address()
    {
        $user = factory(User::class)->create();

        $address = factory(Address::class)->create([
            'user_id' => $user->id,
        ]);

        $shipping = factory(ShippingMethod::class)->create();

        $this->jsonAs($user, 'POST', 'api/orders', [
            'shipping_method_id' => $shipping->id,
            'address_id' => $address->id
        ])->assertJsonValidationErrors(['shipping_method_id']);
    }
...
```

<a name="section-4"></a>

## Episode-85 Creating an order

`1` - Edit `app/Models/User.php`

```php
...
use App\Models\Order;
...
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
```

`2` - Edit `app/Http/Controllers/Orders/OrderController.php`

```php
...
    public function store(OrderStoreRequest $request)
    {
        $request->user()->orders()->create(
            $request->only(['address_id', 'shipping_method_id'])
        );
    }
...
```

`3` Create new migration file `add_subtotal_to_orders_table`

```command
php artisan make:migration add_subtotal_to_orders_table --table=orders
```

`4` - Edit 'database/migrations/2019_07_30_164104_add_subtotal_to_orders_table.php'

```php
...
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('subtotal');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('subtotal');
        });
    }
...
```

`5` - Edit `app/Models/Order.php`

```php
...
//adding subtotal to fillable array
 protected $fillable = [
        'status',
        'address_id',
        'subtotal',
        'shipping_method_id'
    ];
...
```

`6` - Edit `app/Http/Controllers/Orders/OrderController.php`

```php
use App\Cart\Cart;
...
class OrderController extends Controller
{
    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->middleware(['auth:api']);

        $this->cart = $cart;
    }

    public function store(OrderStoreRequest $request)
    {
        $this->createOrder($request);
    }

    protected function createOrder(Request $request)
    {
        return $request->user()->orders()->create(
            array_merge($request->only(['address_id', 'shipping_method_id']), [
                'subtotal' => $this->cart->subtotal()->amount()
            ])
        );
    }
}
```


`7` - Edit `tests/Unit/Models/Users/UserTest.php`

```php
use App\Models\Order;
...
 public function test_it_has_many_orders()
    {
        $user = factory(User::class)->create();

        factory(Order::class)->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(Order::class, $user->orders->first());
    }
...
```

`8` - Edit `database/factories/OrderFactory.php`

```php
// added subtotal
...
$factory->define(Order::class, function (Faker $faker) {
    return [
        'address_id' => factory(Address::class)->create()->id,
        'shipping_method_id' => factory(ShippingMethod::class)->create()->id,
        'subtotal' => 1000
    ];
});
...
```

`9` - Edit `tests/Feature/Orders/OrderStoreTest.php`

```php

...
  public function test_it_can_create_an_order()
    {
        $user = factory(User::class)->create();

        list($address, $shipping) = $this->orderDependencies($user);

        $this->jsonAs($user, "POST", 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id
        ]);
    }

    protected function orderDependencies(User $user)
    {
        $address = factory(Address::class)->create([
            'user_id' => $user->id
        ]);

        $shipping = factory(ShippingMethod::class)->create();
        $shipping->countries()->attach($address->country);

        return [$address, $shipping];
    }
```

<a name="section-5"></a>

## Episode-86 Revisiting orders and product relations

`1` - Edit `App\Models\Order`

```php
use App\Models\ProductVariation;
...
public function products()
    {
       return $this->belongsToMany(ProductVariation::class, 'product_variation_order')
            ->withPivot(['quantity'])
            ->withTimestamps();
    }
```

`2` - Edit `tests/Unit/Models/Orders/OrderTest.php`

```php
use App\Models\ProductVariation;
...
    public function test_it_has_many_products()
    {
        $order = factory(Order::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $order->products()->attach(
            factory(ProductVariation::class)->create(),
            [
                'quantity' => 1
            ]
        );

        $this->assertInstanceOf(ProductVariation::class, $order->products->first());
    }

     public function test_it_has_a_quantity_attached_to_the_product()
    {
        $order = factory(Order::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $order->products()->attach(
            factory(ProductVariation::class)->create(),
            [
                'quantity' => $quantity = 2
            ]
        );

        $this->assertEquals($order->products->first()->pivot->quantity, $quantity);
    }
...
```

<a name="section-6"></a>

## Episode-87 Fixing cart store failing test

`1` - Edit `app/Http/Controllers/Orders/OrderController.php`

- Removing from `__construct()` __Cart__ instance and injecting to __store__ method <br>
and passing it to __createOrder__ method.

```php
...
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function store(OrderStoreRequest $request, Cart $cart)
    {
        $this->createOrder($request, $cart);
    }

    protected function createOrder(Request $request, Cart $cart)
    {
        return $request->user()->orders()->create(
            array_merge($request->only(['address_id', 'shipping_method_id']), [
                'subtotal' => $cart->subtotal()->amount()
            ])
        );
    }
}

```

<a name="section-7"></a>

## Episode-88 Attaching products when ordering

`1` - Edit `app/Http/Controllers/Orders/OrderController.php`

- Filtering the user's ordered products and storing to product_variation_order __pivot__ table

```php
public function store(OrderStoreRequest $request, Cart $cart)
    {
        $order = $this->createOrder($request, $cart);

        $products = $cart->products()->keyBy('id')->map(function ($product) {
            return [
                'quantity' => $product->pivot->quantity
            ];
        })->toArray();

        $order->products()->sync($products);
    }
```

`2` - Edit `app/Cart/Cart.php`

```php
...
    public function products()
    {
        return $this->user->cart;
    }
...
```

`3` - Edit `tests/Unit/Cart/CartTest.php`

```php
...
    public function test_it_returns_products_in_cart()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $user->cart()->attach(
            $product = factory(ProductVariation::class)->create([
                'price' => 1000
            ]),
            [
                'quantity' => 2
            ]
        );

        $this->assertInstanceOf(ProductVariation::class, $cart->products()->first());
    }
...
```

`4` - Edit `tests/Feature/Orders/OrderStoreTest.php`

```php
use App\Models\Stock;
use App\Models\ProductVariation;
...
public function test_it_attaches_the_products_to_the_order()
    {
        $user = factory(User::class)->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        list($address, $shipping) = $this->orderDependencies($user);

        $response = $this->jsonAs($user, "POST", 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id
        ]);

        $this->assertDatabaseHas('product_variation_order', [
            'product_variation_id' => $product->id
        ]);
    }

    protected function productWithStock()
    {
        $product = factory(ProductVariation::class)->create();

        factory(Stock::class)->create([
            'product_variation_id' => $product->id
        ]);

        return $product;
    }
...
```

<a name="section-8"></a>

## Episode-89 Refactoring to a custom collection

`1` - Edit `app/Http/Controllers/Orders/OrderController.php`

```php
...
    public function store(OrderStoreRequest $request, Cart $cart)
    {
        $order = $this->createOrder($request, $cart);

        $order->products()->sync($cart->products()->forSyncing());
    }
...
```

`2` - Edit `app/Models/ProductVariation.php`

```php
use App\Models\Collections\ProductVariationCollection;
...
    public function newCollection(array $models = [])
    {
        return new ProductVariationCollection($models);
    }
```

`3` - Create folder `Colections` to `app/Models`

`4` - Create new file `ProductVariationCollection.php` to `app/Models/Colections`

`5` - Edit `app/Models/Colections/ProductVariationCollection.php`

```php
<?php

namespace App\Models\Collections;

use Illuminate\Database\Eloquent\Collection;

class ProductVariationCollection extends Collection
{
    public function forSyncing()
    {
        return $this->keyBy('id')->map(function ($product) {
            return [
                'quantity' => $product->pivot->quantity
            ];
        })->toArray();
    }
}
```

`6` - Create new __Test__ `ProductVariationCollectionTest` this will be unit test

```command
php artisan make:test Collections\\ProductVariationCollectionTest --unit
```

`7` - Edit `tests/Unit/Collections/ProductVariationCollectionTest.php`

```php
<?php

namespace Tests\Unit\Collections;

use Tests\TestCase;
use App\Models\User;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\WithFaker;

class ProductVariationCollectionTest extends TestCase
{
    public function test_it_can_get_a_syncing_array()
    {
        $user = factory(User::class)->create();

        $user->cart()->attach(
            $product = factory(ProductVariation::class)->create(),
            [
                'quantity' => $quantity = 2
            ]
        );
        $collection = new ProductVariationCollection($user->cart);

        $this->assertEquals($collection->forSyncing(), [
            $product->id => [
                'quantity' => $quantity
            ]
        ]);
    }
}
```
