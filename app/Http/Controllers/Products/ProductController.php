<?php

namespace App\Http\Controllers\Products;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Scoping\Scopes\CategoryScope;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductIndexResource;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::withScopes($this->scopes())->paginate(10);
        
        if(!$products->count() && request()->get('category'))
        {
            $category = Category::whereSlug(request()->category)->firstOrFail();
            return ProductIndexResource::collection($category->chaildProducts()->paginate(10));
        }
        return ProductIndexResource::collection($products);
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    protected function scopes()
    {
        return [
            'category' => new CategoryScope()
        ];
    }
}