# Episodes from 72 to 81

- [72-Country dropdown selector](#section-1)
- [73-Creating Shipping methods](#section-2)
- [74-Hooking up shipping methods to countries](#section-3)
- [75-Getting the right shipping methods for an address](#section-4)
- [76-Using v-model with a shipping methods for an address](#section-5)
- [77-Outputting available shipping methods](#section-6)
- [78-Adding shipping onto the subtotal](#section-7)
- [79-Displaying shipping price and total at checkout](#section-8)
- [80-Fixing shipping error on checkout](#section-9)
- [81-Adding address and shipping method relation to orders](#section-10)

<a name="section-1"></a>

## Episode-72 Country dropdown selector

`1` - Create new file `resources/js/components/form/CountryDropdown.vue`

```js
<template>
  <select @change="changed" class="form-control">
    <option value>Please select country</option>
    <option 
    :value="country.id" 
    v-for="country in countries" 
    :key="country.id">
    { country.name }
    </option>
  </select>
</template>
<script>
export default {
  data() {
    return {
      countries: []
    };
  },
  methods: {
    async getCountries() {
      let response = await axios.get("api/countries");
      this.countries = response.data.data;
    },
    changed($event) {
      this.$emit("input", $event.target.value);
    }
  },
  created() {
    this.getCountries();
  }
};
</script>
```

`2` - Edit `resources/js/components/checkout/addresses/ShippingAddressCreator.vue`

```js
<template>
  ...
    <div class="form-group">
      <label class="lable">Country-Name</label>
      <CountryDropdown v-model="form.country_id"/>
    </div>
  ...
</template>
<script>
import CountryDropdown from "../addresses/CountryDropdown"; // import component
export default {
  data() {
    return {
      form: {
        name: "",
        address_1: "",
        city: "",
        postal_code: "",
        country_id: "",
        default: true
      }
    };
  },
  components: {
    CountryDropdown // than initialize component
  },
  methods: {
    async store() {
      let response = await axios.post("api/addresses", this.form);
      this.$emit("store", response.data.data);
    }
  }
};
</script>
```

<a name="section-2"></a>

## Episode-73 Creating Shipping methods

`1` - Create new Model with Migration files`ShippingMethod`

```command
php artisan make:model Models\\ShippingMethod -m
```

`2` - Edit `database/migrations/2019_07_26_190328_create_shipping_methods_table.php`

```php
...
public function up()
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('price');
            $table->timestamps();
        });
    }
...
```

`3` - Edit `app/Models/ShippingMethod.php`

```php
<?php

namespace App\Models;

use App\Models\Traits\HasPrice;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasPrice;
}

```

`4` - Create new `Factory` file for ShippingMethod Model

```command
php artisan make:factory ShippingMethodFactory
```

`5` - Edit `database/factories/ShippingMethodFactory.php`

```php
<?php

use Faker\Generator as Faker;
use App\Models\ShippingMethod;

$factory->define(ShippingMethod::class, function (Faker $faker) {
    return [
        'name' => 'Royal Mail',
        'price' => 1000
    ];
});

```

`6` - Create new `UnitTest` file for `ShippingMethod` model

```command
php artisan make:test Models\\ShippingMethods\\ShippingMethodTest --unit
```

`7` - Edit `tests/Unit/Models/ShippingMethods/ShippingMethodTest.php`

```php
<?php

namespace Tests\Unit\Models\ShippingMethods;

use App\Cart\Money;
use Tests\TestCase;
use App\Models\ShippingMethod;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingMethodTest extends TestCase
{

    public function test_it_returns_a_money_instance_for_the_price()
    {
        $shipping = factory(ShippingMethod::class)->create();

        $this->assertInstanceOf(Money::class, $shipping->price);
    }

    public function test_it_returns_a_formatted_price()
    {
        $shipping = factory(ShippingMethod::class)->create([
            'price' => 0
        ]);

        $this->assertEquals($shipping->formattedPrice, '£0.00');
    }
}

```

<a name="section-3"></a>

## Episode-74 Hooking up shipping methods to countries

`1` - Create new Migration file `Country-Shipping-Method` this will be a pivot table

```command
php artisan make:migration create_country_shipping_method_table --create=country_shipping_method
```

`2` - Edit `database/migrations/2019_07_26_195923_create_country_shipping_method_table.php`

```php
...
public function up()
  {
      Schema::create('country_shipping_method', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->bigInteger('country_id')
            ->unsigned()
            ->index();
          $table->bigInteger('shipping_method_id')
            ->unsigned()
            ->index();
      });

      Schema::table('country_shipping_method', function (Blueprint $table) {
          $table->foreign('country_id')
            ->references('id')
            ->on('countries');
          $table->foreign('shipping_method_id')
            ->references('id')
            ->on('shipping_methods');
      });
  }
...
```

`3` - Edit `app/Models/ShippingMethod.php` 

```php
<?php

namespace App\Models;

use App\Models\Country;
...
    public function countries()
    {
        return $this->belongsToMany(Country::class);
    }
...
```

`4` - Edit `app/Models/Country.php`

```php
<?php

namespace App\Models;

use App\Models\ShippingMethod;
...
    public function shippingMethods()
    {
        return $this->belongsToMany(ShippingMethod::class);
    }
...
```

`5` - Edit `tests/Unit/Models/ShippingMethods/ShippingMethodTest.php`

```php

use App\Models\Country;
...
 public function test_it_belongs_to_many_countries()
    {
        $shipping = factory(ShippingMethod::class)->create();

        $shipping->countries()->attach(
            factory(Country::class)->create()
        );

        $this->assertInstanceOf(Country::class, $shipping->countries->first());
    }
...
```

`6` - Create new Test file `CountryTest`

```command
php artisan make:test Models\\Countries\\CountryTest --unit
```

`7` - Edit `tests/Unit/Models/Countries/CountryTest.php`

```php
<?php

namespace Tests\Unit\Models\Countries;

use Tests\TestCase;
use App\Models\Country;
use App\Models\ShippingMethod;

class CountryTest extends TestCase
{
    public function test_it_has_many_shipping_methods()
    {
        $country = factory(Country::class)->create();

        $country->shippingMethods()->attach(
            factory(ShippingMethod::class)->create()
        );

        $this->assertInstanceOf(ShippingMethod::class, $country->shippingMethods->first());
    }
}
```

<a name="section-4"></a>

## Episode-75 Getting the right shipping methods for an address

`1` - Create new Controller file `AddressShippingController`

```command
php artisan make:controller Addresses\\AddressShippingController
```

`2` - Edit `app/Http/Controllers/Addresses/AddressShippingController.php`

```php
<?php

namespace App\Http\Controllers\Addresses;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddressShippingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }
    public function action(Address $address)
    {
        return ShippingMethodResource::collection(
            $address->country->shippingMethods
        );
    }
}
```

`3` - Edit `routes/api.php` add new router for AddressShippingController's an action method

```php
...
Route::get('addresses/{address}/shipping', 'Addresses\AddressShippingController@action');
...
```

`4` - Create new Resource file `ShippingMethodResource`

```command
php artisan make:resource ShippingMethodResource 
```

`5` - Edit `app/Http/Resources/ShippingMethodResource.php`

```php
...
  public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->formattedPrice
        ];
    }
...
```

`6` - Create new Policy file `AddressPolicy`

```command
php artisan make:policy AddressPolicy
```

`7` - Edit 'app/Providers/AuthServiceProvider.php' register policy just created

```php
...
  protected $policies = [
      'App\Models\Address' => 'App\Policies\AddressPolicy', // registring Policy
  ];
...
```

`8` - Edit `app/Policies/AddressPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Address;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    public function show(User $user, Address $address)
    {
        return $user->id == $address->user_id;
    }
}
```

`9` - Edit `app/Http/Controllers/Addresses/AddressShippingController.php`

```php
...
 public function action(Address $address)
    {
        // using policy if user authenticated and equals address->user_id return true
        $this->authorize('show', $address); 
        ...
    }
...
```

`10` - Create new Test file `AddressShippingTest`

```command
php artisan make:test Addresses\\AddressShippingTest
```

`11` - Edit `tests/Feature/Addresses/AddressShippingTest.php`

```php
<?php

namespace Tests\Feature\Addresses;

use Tests\TestCase;
use App\Models\User;
use App\Models\Address;
use App\Models\Country;
use App\Models\ShippingMethod;

class AddressShippingTest extends TestCase
{
    public function test_it_fails_if_the_user_is_not_authenticated()
    {
        $this->json('GET', 'api/addresses/1/shipping')
            ->assertStatus(401);
    }

    public function test_it_fails_if_the_address_cant_be_found()
    {
        $user = factory(User::class)->create();
        $this->jsonAs($user, 'GET', 'api/addresses/1/shipping')
            ->assertStatus(404);
    }

    public function test_it_fails_if_the_address_does_not_belong_to_the_user()
    {
        $user = factory(User::class)->create();
        $address = factory(Address::class)->create([
            'user_id' => factory(User::class)->create()->id
        ]);

        $this->jsonAs($user, 'GET', "api/addresses/{$address->id}/shipping")
            ->assertStatus(403);
    }

    public function test_it_shows_shipping_methods_for_the_given_address()
    {
        $user = factory(User::class)->create();
        $address = factory(Address::class)->create([
            'user_id' => $user->id,
            'country_id' => ($country = factory(Country::class)->create())->id
        ]);
        $country->shippingMethods()->save(
            $shipping = factory(ShippingMethod::class)->create()
        );
        $this->jsonAs($user, 'GET', "api/addresses/{$address->id}/shipping")
            ->assertJsonFragment([
                'id' => $shipping->id
          ]);
    }
}
```

<a name="section-5"></a>

## Episode-76 Using v-model with a shipping methods for an address

`1` - Edit `resources/js/pages/checkout/index.vue`

- Binding with `v-model` method current page `form.address_id` data to ShippingAddress page `selectedAddress` data

```js
 <ShippingAddress :addresses="address"></ShippingAddress>
 ...
<script>
...
export default {
  data() {
    return {
      address: []
    };
  },
```

change to

```js
 <ShippingAddress :addresses="address" v-model="form.address_id"></ShippingAddress>
 ...
<script>
...
export default {
  data() {
    return {
      address: [],
      form: {
        address_id: null
      }
    };
  },
```

`2` - Edit `resources/js/components/checkout/addresses/ShippingAddress.vue`

```js
...
watch: {
    defaultAddress(v) {
      if (v) {
        this.switchAddress(v);
      }
    }
  },
...
```

change to

```js
watch: {
    defaultAddress(v) {
      if (v) {
        this.switchAddress(v);
      }
    },
    selectedAddress(address) {
      this.$emit("input", address.id);
    }
  },
```
<a name="section-6"></a>

## Episode-77 Outputting available shipping methods

`1` - Edit `resources/js/pages/checkout/index.vue`

- adding __Shipping__ section

```vue
...
  <article class="pl-4">
    <h3 class="text-muted pt-2 mb-3">Shipping</h3>
    <div class="form-group">
      <select class="form-control" v-model="form.shipping_method_id">
        <option
          v-for="shipping in shippingMethods"
          :key="shipping.id"
        >{ shipping.name } ({ shipping.price })</option>
      </select>
    </div>
  </article>
...
```

```js
<script>
...
export default {
  data() {
    return {
      address: [],
      shippingMethods: [],
      form: {
        address_id: null,
        shipping_method_id: null
      }
    };
  },
  watch: {
    "form.address_id"(addressId) {
      this.getShippingMethodsForAddress(addressId);
    }
  },
  ...
  methods: {
     ...
     async getShippingMethodsForAddress(addressId) {
      let response = await axios.get(`api/addresses/${addressId}/shipping`);
      this.shippingMethods = response.data.data;
    }
  }
```

<a name="section-7"></a>

## Episode-78 Adding shipping onto the subtotal

`1` - Edit `app/Http/Controllers/Cart/CartController.php`

```php
...
 public function index(Request $request, Cart $cart)
    {
     ...
      return (new CartResource($request->user()))
          ->additional([
              'meta' => $this->meta($cart, $request)  //add request as a second parametr
        ]);
    }
  protected function meta(Cart $cart, Request $request) // add request Instance
    {
      return [
        'empty' => $cart->isEmpty(),
        'subtotal' => $cart->subtotal()->formatted(),
        'total' => $cart->withShipping($request->shipping_method_id)->total()->formatted(), // change this line
        'changed' => $cart->hasChanged()
      ];
    }

```

`2` - Edit `app/Cart/Cart.php`

```php
use App\Models\ShippingMethod;
...
class Cart
{
  protected $shipping;
  ...
  // add new withShipping method
  public function withShipping($shippingId)
  {
      $this->shipping = ShippingMethod::find($shippingId);
        return $this;
  }
  ...
  
  public function total()
  {
    // add this condition if shipping is exists return (total + shipping) amount
    if ($this->shipping) {
      return $this->subtotal()->add($this->shipping->price);
    }
      return $this->subtotal();
    }
  ...
```

`3` - Edit `app/Cart/Money.php`

```php
...
class Money
{
  ...
  public function add(Money $money)
    {
        $this->money = $this->money->add($money->instance());

        return $this;
    }

    public function instance()
    {
        return $this->money;
    }
}

```

`4` - Edit `tests/Unit/Cart/CartTest.php`

```php
use App\Models\ShippingMethod;
...
class CartTest extends TestCase
{
...
  public function test_it_can_return_the_correct_total_without_shipping()
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

        $this->assertEquals($cart->total()->amount(), 2000);
    }

  public function test_it_can_return_the_correct_total_with_shipping()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $shipping = factory(ShippingMethod::class)->create([
            'price' => 1000
        ]);

        $user->cart()->attach(
            $product = factory(ProductVariation::class)->create([
                'price' => 1000
            ]),
            [
                'quantity' => 2
            ]
        );

        $cart = $cart->withShipping($shipping->id);

        $this->assertEquals($cart->total()->amount(), 3000);
    }
}
```

`5` - Create new Test `MoneyTest` this will be unit test

```command
php artisan make:test Money\\MoneyTest --unit
```

`6` - Edit `tests/Unit/Money/MoneyTest.php`

```php
<?php

namespace Tests\Unit\Money;

use App\Cart\Money;
use Tests\TestCase;
use Money\Money as BaseMoney;

class MoneyTest extends TestCase
{
    public function test_it_can_get_the_row_amount()
    {
        $money = new Money(1000);

        $this->assertEquals($money->amount(), 1000);
    }

    public function test_it_can_get_the_formatted_amount()
    {
        $money = new Money(1000);

        $this->assertEquals($money->formatted(), '£10.00');
    }

    public function test_it_can_add_up()
    {
        $money = new Money(1000);

        $money = $money->add(new Money(1000));

        $this->assertEquals($money->amount(), 2000);
    }

    public function test_it_can_return_the_underlying_instance()
    {
        $money = new Money(1000);

        $this->assertInstanceOf(BaseMoney::class, $money->instance());
    }
}
```

`7` - Edit `tests/Feature/Cart/CartIndexTest.php`

```php
use App\Models\ShippingMethod;
...
class CartIndexTest extends TestCase
{
...
 public function test_is_shows_a_formatted_total_with_shipping()
    {
        $user = factory(User::class)->create();

        $shipping = factory(ShippingMethod::class)->create([
            'price' => 1000
        ]);

        $response = $this->jsonAs($user, 'GET', "api/cart?shipping_method_id={$shipping->id}")
            ->assertJsonFragment([
                'total' => "£10.00"
            ]);
    }
}
```