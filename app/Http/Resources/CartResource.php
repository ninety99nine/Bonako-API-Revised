<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class CartResource extends BaseResource
{
    protected $customExcludeFields = ['instant_cart_id', 'location_id', 'owner_id', 'owner_type'];

    /**
     *  When iterating over a collection, the constructor will receive the
     *  resource as the first parameter and then the index number as the
     *  second parameter. Note that the index is provided only if this
     *  resource is part of a resource collection, otherwise we
     *  default to null.
     */
    public function __construct($resource, $collectionIndex = null)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return $this->transformedStructure();

    }

    public function setLinks()
    {
    }
}
