<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'description'
    ];
    
    public function getRouteKeyName(){
        return 'slug';
    }
}