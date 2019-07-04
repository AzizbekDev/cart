<?php

namespace App\Http\Controllers\Categories;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(
            // Category::get()
            
            // Category get parent with children & ordered by order column ascending
            Category::with('children')->parents()->ordered()->get()
        );
    }
}
