<?php
namespace App\Models\Traits;

trait HasChaildProducts
{
    public function chaildProducts()
    {
        $collection = $this->children->map(function ($query){
        return $query->products->map(function ($product){
            return $product;
        });
        })->flatten();
        return $collection;
    }
}