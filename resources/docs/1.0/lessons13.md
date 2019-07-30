# Episodes from 122 to 125

- [122-Using Mockery to test more complex listeners](#section-1)
- [123-Client authentication middleware](#section-2)
- [124-Tweaking order status components](#section-3)
- [125-Storing transactions](#section-4)

<a name="section-1"></a>

## Episode-122 Using Mockery to test more complex listeners

`1` - Create new test file `ProcessPaymentListenerTest` this will be unit test

```command
php artisan make:test Listeners\\ProcessPaymentListenerTest --unit
```

`2` - Edit `tests/Unit/Listeners/ProcessPaymentListenerTest.php`

```php
<?php

namespace Tests\Unit\Listeners;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Events\Order\OrderPaid;
use App\Events\Order\OrderCreated;
use App\Events\Order\OrderPaymentFaild;
use App\Listeners\Order\ProcessPayment;
use App\Exceptions\PaymentFaildException;
use App\Cart\Payments\Gateways\StripeGateway;
use App\Cart\Payments\Gateways\StripeGatewayCustomer;

class ProcessPaymentListenerTest extends TestCase
{
    public function test_it_charges_payment_the_correct_amount()
    {
        \Event::fake();

        list($user, $payment, $event, $order) = $this->createEvent();

        list($gateway, $customer) = $this->mockFlow();

        $customer->shouldReceive('charge')->with(
            $order->paymentMethod,
            $order->total()->amount()
        );

        $listener = new ProcessPayment($gateway);

        $listener->handle($event);
    }

    public function test_it_fires_the_order_paid_event()
    {

        \Event::fake();

        list($user, $payment, $event, $order) = $this->createEvent();

        list($gateway, $customer) = $this->mockFlow();

        $customer->shouldReceive('charge')->with(
            $order->paymentMethod,
            $order->total()->amount()
        );

        $listener = new ProcessPayment($gateway);

        $listener->handle($event);

        \Event::assertDispatched(OrderPaid::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    public function test_it_fires_the_order_failed_event()
    {

        \Event::fake();

        list($user, $payment, $event, $order) = $this->createEvent();

        list($gateway, $customer) = $this->mockFlow();

        $customer->shouldReceive('charge')->with(
            $order->paymentMethod,
            $order->total()->amount()
        )
            ->andThrow(PaymentFaildException::class);

        $listener = new ProcessPayment($gateway);

        $listener->handle($event);

        \Event::assertDispatched(OrderPaymentFaild::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }


    protected function createEvent()
    {
        $user = factory(User::class)->create();

        $payment = factory(PaymentMethod::class)->create([
            'user_id' => $user->id
        ]);

        $event = new OrderCreated(
            $order = factory(Order::class)->create([
                'user_id' => $user->id,
                'payment_method_id' => $payment->id
            ])
        );

        return [$user, $payment, $event, $order];
    }

    protected function mockFlow()
    {
        $gateway = Mockery::mock(StripeGateway::class);

        $gateway->shouldReceive('withUser')
            ->andReturn($gateway)
            ->shouldReceive('getCustomer')
            ->andReturn(
                $customer = Mockery::mock(StripeGatewayCustomer::class)
            );
        return [$gateway, $customer];
    }
}
```

<a name="section-2"></a>

## Episode-123 Client authentication middleware `Nuxt`

`1` - Edit `resources/js/pages/orders/index.vue`

```js
<script>
...
middleware: [
   'redirectIfGuest'
  ],
...
</script>
```

`2` - Create new folder `middleware` into `resources/js`

`3` - Create new file `redirectIfGuest.js` into `resources/js/middleware`

`4` - Edit `resources/js/middleware/redirectIfGuest.js`

```js
export default function({app, redirect, route})
{
if(!app.$auth.loggedIn){
    return redirect({
        name: 'auth-signin',
        query:{
            redirect: route.fullPath
        }
    });
}
}
```

`5` - Edit `resources/js/pages/auth/Login.vue`

```js
<script>
...
    middleware:[
        'redirectIfAuthenticated'
    ],
    methods: {
    ...
    async signin(){
        await this.$auth.loginWith('local',{
            data: this.form
        })
        if(this.$route.query.redirect){
            this.$router.replace(this.$route.query.redirect)
            return
        }
        this.$router.replace({
            name: 'index'
        })
    }
  },
</script>
```

`6` - Edit `resources/js/pages/cart/index.vue`

```js
<script>
...
middleware:[
    'redirectIfGuest'
],
...
</script>
```

`7` - Edit `resources/js/pages/checkout/index.vue`

```js
<script>
...
middleware:[
    'redirectIfGuest'
],
...
</script>
```

`8` - Create new file and edit `redirectIfAuthenticated.js` into `resources/js/middleware`

```js
export default function({ app, redirect, route })
{
    if(app.$auth.loggedIn){
        return redirect({
            name: 'index'
        })
    }
}
```
