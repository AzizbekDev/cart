<?php

namespace App\Http\Resources;

use App\Http\Resources\ProductIndexResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends ProductIndexResource
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request),[
            'price' => $this->price,
            'variables' => []
        ]);
    }
}
