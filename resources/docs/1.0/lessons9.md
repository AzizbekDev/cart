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
