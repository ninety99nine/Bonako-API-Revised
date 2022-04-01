<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class ProductResource extends BaseResource
{
    protected $customExcludeFields = ['user_id', 'parent_product_id'];

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
        /**
         *  Viewing as Public User
         *
         *  If we are veiwing as the general public then limit the information we share.
         */
        if( $this->isPublicUser ) {

            //  Overide and apply custom fields
            $this->customFields = [];

            //  Overide and apply custom attributes
            $this->customAttributes = [];

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        //  Links for the Public User
        if( $this->isPublicUser ) {

            $this->resourceLinks = [

                //  new ResourceLink('self', route('users.show', ['user' => $this->resource->id]), 'The public user profile'),

            ];

        //  Links for the Authourized User & Super Admin
        }else{

            /**
             *  Check if this request is being performed by the Authourized User
             *  or by the Super Admin on behalf of a user. Use this to determine
             *  the route name prefix to generate the correct links.
             */
            $routeNamePrefix = $this->isAuthourizedUser ? 'auth.user.' : 'user.';

            $this->resourceLinks = [
                //  new ResourceLink('self', route($routeNamePrefix.'profile.show', $this->isAuthourizedUser ? [] : ['user' => $this->resource->id]), 'The private user profile')
            ];

        }
    }
}
