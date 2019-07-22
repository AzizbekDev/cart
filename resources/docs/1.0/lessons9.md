# Episodes from 82 to 91

- [82-Order statuses and defaults](#section-1)
- [83-Basic order validation](#section-2)
- [84-Custom shipping method validation rule](#section-3)
- [85-Creating an order](#section-4)
- [86-Revisiting orders and product relations](#section-5)
- [87-Fixing cart store failing test](#section-6)
- [88-Attaching products when ordering](#section-8)
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