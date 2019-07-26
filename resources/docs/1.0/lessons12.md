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
