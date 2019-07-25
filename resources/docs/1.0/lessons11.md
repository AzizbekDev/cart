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
use App\Http\Resources\ProductIndexResource;
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
