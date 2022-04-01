<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResources;

class ProductResources extends BaseResources
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\ProductResource';
}
