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

