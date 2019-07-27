# Episodes from 112 to 121

- [112-Mocking up our payment gateway](#section-1)
- [113-Storing a payment method](#section-2)
- [114-Responding with a card and writing some tests](#section-3)
- [115-Storing a new card with Stripe](#section-4)
- [116-Event handler for processing the payment](#section-5)
- [117-Processing a payment](#section-6)
- [118-Handling a failed payment](#section-7)
- [119-Handling a successful payment](#section-8)
- [120-Fixing failing 'cart empty' test](#section-9)
- [121-Testing listeners](#section-10)

<a name="section-1"></a>

## Episode-112 Mocking up our payment gateway

`1` -  `app/Http/Controllers/PaymentMethods/PaymentMethodController.php`

```php
<?php
...
use App\Cart\Payments\Gateway;
...

class PaymentMethodController extends Controller
{
    protected $gateway;

    public function __construct(Gateway $gateway)
    {
        ..
        $this->gateway = $gateway;
    }
    ...
    public function store(Request $request)
    {
        $cart = $this->gateway->withUser($request->user())
            ->createCustomer()
            ->addCard($request->token);
    }
}
```

`2` - Create new folder `Payments` into `app/Cart`

`3` - Create new file `Gateway` into `app/Cart/Payments` this will be interface

`4` - Edit `app/Cart/Payments/Gateway.php`

```php
<?php

namespace App\Cart\Payments;

use App\Models\User;

interface Gateway
{
    public function withUser(User $user);

    public function createCustomer();
}
```

`5` - Create new folder `Gateways` into `app/Cart/Payments`

`6` - Create new file `StripeGateway.php` into `app/Cart/Payments/Gateways`

`7` - Edit `app/Cart/Payments/Gateways/StripeGateway.php`

```php
<?php

namespace App\Cart\Payments\Gateways;

use App\Models\User;
use App\Cart\Payments\Gateway;
use App\Cart\Payments\Gateways\StripeGatewayCustomer;

class StripeGateway implements Gateway
{
    protected $user;

    public function withUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function createCustomer()
    {
        return new StripeGatewayCustomer();
    }
}
```

`8` - Edit `app/Providers/AppServiceProvider.php`

```php
use App\Cart\Payments\Gateway;
use App\Cart\Payments\Gateways\StripeGateway;
...
    public function register()
    {
        ...

        $this->app->singleton(Gateway::class, function () {
            return new StripeGateway();
        });
    }
...
```

`9` - Create new file `GatewayCustomer` into `app/Cart/Payments` this will be interface

`10` - Edit `app/Cart/Payments/GatewayCustomer.php`

```php
<?php

namespace App\Cart\Payments;

use App\Models\PaymentMethod;

interface GatewayCustomer
{
    public function charge(PaymentMethod $cart, $amount);

    public function addCard($token);
}
```

`11` - Create new file `StripeGatewayCustomer.php` into `app/Cart/Payments/Gateways`

`12` - Edit `app/Cart/Payments/Gateways/StripeGatewayCustomer.php`

```php
<?php

namespace App\Cart\Payments\Gateways;

use App\Models\PaymentMethod;
use App\Cart\Payments\GatewayCustomer;

class StripeGatewayCustomer implements GatewayCustomer
{
    public function charge(PaymentMethod $cart, $amount)
    {
        //
    }

    public function addCard($token)
    {
        //
    }
}
```

<a name="section-2"></a>

## Episode-113 Storing a payment method

`1` - Create new migration file `add_gateway_customer_id_to_users_table`

```command
php artisan make:migration add_gateway_customer_id_to_users_table --table=users
```

`2` - Edit `database/migrations/2019_08_05_072518_add_gateway_customer_id_to_users_table.php`

```php
...
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gateway_customer_id')->nullable();
        });
    }
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gateway_customer_id');
        });
    }
...
```

`3` - Edit `app/Http/Controllers/PaymentMethods/PaymentMethodController.php`

```php
...
public function index(Request $request)
    {
        return PaymentMethodResource::collection(
            $request->user()->paymentMethods
        );
    }
public function store(Request $request)
    {
        $cart = $this->gateway->withUser($request->user())
            ->createCustomer()
            ->addCart($request->token);

        dd($cart);
    }
...
```

`4` - Edit `app/Models/User.php`

- Added `gateway_customer_id` to fillable array

```php
...
protected $fillable = [
        'name', 'email', 'password', 'gateway_customer_id'
    ];
...
```

`5` - Edit `app/Cart/Payments/GatewayCustomer.php`

```php
...
interface GatewayCustomer
{
   ...
    public function addCart($token);
    public function id();
}
```

`6` - Edit `app/Cart/Payments/Gateways/StripeGatewayCustomer.php`

```php
use App\Cart\Payments\Gateway;
use Stripe\Customer as StripeCustomer;
...
    protected $gateway;
    protected $customer;

    public function __construct(Gateway $gateway, StripeCustomer $customer)
    {
        $this->gateway = $gateway;
        $this->customer = $customer;
    }

   ...

    public function addCart($token)
    {
        $cart = $this->customer->sources->create([
            'source' => $token,
        ]);
        $this->customer->default_source = $cart->id;

        $this->customer->save();

        $this->gateway->user()->paymentMethods()->create([
            'cart_type' => $cart->brand,
            'last_four' => $cart->last4,
            'provider_id' => $cart->id,
            'default' => true
        ]);
    }

    public function id()
    {
        return $this->customer->id;
    }
...
```

`7` - Edit `app/Cart/Payments/Gateways/StripeGateway.php`

```php
use Stripe\Customer as StripeCustomer;
...
    public function user()
    {
        return $this->user;
    }

    public function createCustomer()
    {
        if ($this->user->gateway_customer_id) {
            return $this->getCustomer();
        }

        $customer = new StripeGatewayCustomer(
            $this,
            $this->createStripeCustomer()
        );

        $this->user->update([
            'gateway_customer_id' => $customer->id()
        ]);

        return $customer;
    }

    protected function getCustomer()
    {
        return new StripeGatewayCustomer(
            $this,
            StripeCustomer::retrieve($this->user->gateway_customer_id)
        );
    }

    protected function createStripeCustomer()
    {
        return StripeCustomer::create([
            'email' => $this->user->email
        ]);
    }
...
```

<a name="section-3"></a>

## Episode-114 Responding with a card and writing some tests

`1` - Edit `app/Cart/Payments/Gateways/StripeGatewayCustomer.php`

- added return to getting created paymentMethod instance

```php
...
public function addCart($token)
    {
        return $this->gateway->user()->paymentMethods()->create([
            'cart_type' => $cart->brand,
            'last_four' => $cart->last4,
            'provider_id' => $cart->id,
            'default' => true
        ]);
    }
...
```

`2` - Edit `app/Http/Controllers/PaymentMethods/PaymentMethodController.php`

```php
public function store(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
        ...
        return new PaymentMethodResource($cart);
    }
```

`3` - Create new test `PaymentMethodStoreTest`

```command
php artisan make:test PaymentMethods\\PaymentMethodStoreTest
```

`4` - Edit `tests/Feature/PaymentMethods/PaymentMethodStoreTest.php`

```php
<?php
namespace Tests\Feature\PaymentMethods;

use Tests\TestCase;
use App\Models\User;

class PaymentMethodStoreTest extends TestCase
{
    public function test_it_fails_if_not_authenticated()
    {
        $this->json("POST", "api/payment-methods")
            ->assertStatus(401);
    }

    public function test_it_requires_a_token()
    {
        $user = factory(User::class)->create();

        $this->jsonAs($user, "POST", "api/payment-methods")
            ->assertJsonValidationErrors(['token']);
    }

    public function test_it_can_successfully_add_a_card()
    {
        $user = factory(User::class)->create();

        $this->jsonAs($user, 'POST', 'api/payment-methods', [
            'token' => 'tok_visa'
        ]);

        $this->assertDatabaseHas('payment_methods', [
            'user_id' => $user->id,
            'cart_type' => 'Visa',
            'last_four' => '4242'
        ]);
    }

    public function test_it_returns_the_created_card()
    {
        $user = factory(User::class)->create();

        $this->jsonAs($user, 'POST', 'api/payment-methods', [
            'token' => 'tok_visa'
        ])
            ->assertJsonFragment([
                'cart_type' => 'Visa'
            ]);
    }

    public function test_it_sets_the_created_card_as_default()
    {
        $user = factory(User::class)->create();

        $response = $this->jsonAs($user, 'POST', 'api/payment-methods', [
            'token' => 'tok_visa'
        ]);
        $this->assertDatabaseHas('payment_methods', [
            'id' => json_decode($response->getContent())->data->id,
            'default' => true
        ]);
    }
}
```

`5` - Create new request file `PaymentMethodStoreRequest`

```command
php artisan make:request PaymentMethods\\PaymentMethodStoreRequest
```

`6` - Edit `app/Http/Requests/PaymentMethods/PaymentMethodStoreRequest.php`

```php
...
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'token' => 'required'
        ];
    }
...
```

`7` - Edit `app/Http/Controllers/PaymentMethods/PaymentMethodController.php`

```php
use App\Http\Requests\PaymentMethods\PaymentMethodStoreRequest;
...
public function store(PaymentMethodStoreRequest $request)
    {
        $cart = $this->gateway->withUser($request->user())
            ->createCustomer()
            ->addCart($request->token);

        return new PaymentMethodResource($cart);
    }
...
```

<a name="section-4"></a>

## Episode-115 Storing a new card with Stripe

`1` - Edit `resources/views/layouts/app.blade.php`

```html
...
<script src="https://js.stripe.com/v3/"></script>
...
```

`2` - Create new file `PaymentMethodCreator.vue` into `resources/js/components/checkout/paymentMethods` and edit

```html
<template>
  <form action="#" @submit.prevent="store">
    <div class="form-group">
      <label for="card-element">Credit or debit card</label>
      <div id="card-element" class="form-control"></div>
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-primary btn-sm" :disabled="storing">Store card</button>
      <button type="button" class="btn btn-light btn-sm" @click.prevent="$emit('cancel')">Cancel</button>
    </div>
  </form>
</template>
```

js part

```js
<script>
export default {
  data() {
    return {
      stripe: null,
      card: null,
      storing: false
    };
  },
  methods: {
    async store() {
      this.storing = true;
      const { token, error } = await this.stripe.createToken(this.card);
      if (error) {
        //
      } else {
        let response = await axios.post("api/payment-methods", {
          token: token.id
        });
        this.$emit("added", response.data);
      }
      this.storing = false;
    }
  },
  mounted() {
    const stripe = Stripe("pk_test_EzuIlgdPvUDCa2ScLlsdo06j00qtkWwqZe");
    this.stripe = stripe;
    this.card = stripe.elements().create("card");
    this.card.mount("#card-element");
  }
};
</script>
```

`3` - Edit `resources/js/components/checkout/paymentMethods/PaymentMethods.vue`

```html
...
<template v-else-if="creating">
    <PaymentMethodCreator
        @cancel="creating = false"
        @added="created" />
</template>
...
<button type="button" class="btn btn-primary btn-sm"
    @click.prevent="selecting = true"
    v-if="paymentMethods.length"
    >Change payment method
</button>
...
```

js part

```js
import PaymentMethodCreator from "../paymentMethods/PaymentMethodCreator";
...
components: {
    ...
    PaymentMethodCreator
  },
...
```
<a name="section-5"></a>

## Episode-116 Event handler for processing the payment

`1` - Edit `app/Providers/EventServiceProvider.php`

- added new `ProcessPayment` listener

```php
...
   protected $listen = [
        ...
        'App\Events\Order\OrderCreated' => [
            'App\Listeners\Order\ProcessPayment',
            ...
        ]
    ];
...
```

`2` - Create new Listeners file `ProcessPayment.php` into `app/Listeners/Order`

`3` - Edit `app/Listeners/Order/ProcessPayment.php`

```php
<?php

namespace App\Listeners\Order;

use App\Cart\Payments\Gateway;
use App\Events\Order\OrderCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessPayment implements ShouldQueue
{
    protected $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function handle(OrderCreated $event)
    {
        // with user x
        // get customer
        // charge
    }
}

```

`4` - Edit `app/Models/Order.php`

```php
use App\Models\PaymentMethod;
...
public function paymentMethod()
{
    return $this->belongsTo(PaymentMethod::class);
}
...
```

`5` - Edit `tests/Unit/Models/Orders/OrderTest.php`

```php
use App\Models\PaymentMethod;
...
public function test_it_belongs_to_a_payment_method()
{
    $order = factory(Order::class)->create([
        'user_id' => factory(User::class)->create()->id
    ]);

    $this->assertInstanceOf(PaymentMethod::class, $order->paymentMethod);
}
...
```
