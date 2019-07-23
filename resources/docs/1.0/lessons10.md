# Episodes from 92 to 101

- [92-Returning order details](#section-1)
- [93-Fixing up failing order test](#section-2)
- [94-Placeing orders from the checkout](#section-3)
- [95-Warning users of cart changes, plus some refactoring](#section-4)
- [96-Alerting on checkout changes](#section-5)
- [97-Fixing the quantity UI bug](#section-6)
- [98-Orders endpoint](#section-7)
- [99-Formatting order total and subtotal](#section-9)
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
