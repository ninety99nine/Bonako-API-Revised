<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class LocationResource extends BaseResource
{
    protected $customExcludeFields = ['user_id', 'store_id'];

    public function toArray($request)
    {
        /**
         *  Viewing as Public User
         *
         *  If we are veiwing as the general public
         *  then limit the information we share.
         */
        if( $this->isPublicUser ) {

            //  Overide and apply custom fields
            $this->customFields = ['name', 'call_to_action', 'online', 'offline_message'];

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

                new ResourceLink('self', route('locations.show', ['location' => $this->resource->id]), 'The public location'),

            ];

        //  Links for the Authourized User & Super Admin
        }else{

            /**
             *  Check if this request is being performed by the Authourized User
             *  or by the Super Admin on behalf of a user. Use this to determine
             *  the route name prefix to generate the correct links.
             */
            $routeNamePrefix = $this->isAuthourizedUser ? 'auth.user.location.' : 'location.';

            $this->resourceLinks = [
                new ResourceLink('self', route($routeNamePrefix.'show', ['location' => $this->resource->id]), 'The users location'),
                new ResourceLink('orders', route($routeNamePrefix.'orders', ['location' => $this->resource->id]), 'The users location orders'),
                new ResourceLink('products', route($routeNamePrefix.'products', ['location' => $this->resource->id]), 'The users location products'),
                new ResourceLink('customers', route($routeNamePrefix.'customers', ['location' => $this->resource->id]), 'The users location customers'),
                new ResourceLink('team.members', route($routeNamePrefix.'team.members', ['location' => $this->resource->id]), 'The users location team members'),
                new ResourceLink('instant.carts', route($routeNamePrefix.'instant.carts', ['location' => $this->resource->id]), 'The users location instant carts'),
                new ResourceLink('confirm.delete', route($routeNamePrefix.'confirm.delete', ['location' => $this->resource->id]), 'The route to request a delete confirmation code'),
            ];

        }
    }
}
