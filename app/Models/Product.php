<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Traits\HasPrice;
use App\Models\ProductVariation;
use App\Models\Traits\CanBeScoped;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use CanBeScoped, HasPrice;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'price',
        'description'
    ];
    /**
     * Define the route KeyName
     *
     * @return String
     */    
    public function getRouteKeyName(){
        return 'slug';
    }
    /**
     * cheack stock count
     *
     * @return Boolean
     */
    public function inStock()
    {
        return $this->stockCount() > 0;
    }

    /**
     * calling ProductVariation model stockCount method
     *
     * @return \Collection of its amount
     */
    public function stockCount()
    {
        return $this->variations->sum(function($variation){
            return $variation->stockCount();
        });
    }
    
    /**
     * relation replay product categories
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    
    /**
     * relation replay product variations
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('order', 'asc');
    }
}