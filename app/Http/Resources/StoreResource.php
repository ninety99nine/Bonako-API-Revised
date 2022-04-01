<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class StoreResource extends BaseResource
{
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
            $this->customFields = ['name', 'online', 'offline_message'];

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

                new ResourceLink('self', route('store.show', ['store' => $this->resource->id]), 'The public store'),

            ];

        //  Links for the Authourized User & Super Admin
        }else{

            /**
             *  Check if this request is being performed by the Authourized User
             *  or by the Super Admin on behalf of a user. Use this to determine
             *  the route name prefix to generate the correct links.
             */
            $routeNamePrefix = $this->isAuthourizedUser ? 'auth.user.store.' : 'store.';

            $this->resourceLinks = [
                new ResourceLink('self', route($routeNamePrefix.'show', ['store' => $this->resource->id]), 'The users store'),
                new ResourceLink('locations', route($routeNamePrefix.'locations', ['store' => $this->resource->id]), 'The store locations'),
                new ResourceLink('confirm.delete', route($routeNamePrefix.'confirm.delete', ['store' => $this->resource->id]), 'The route to request a delete confirmation code'),
            ];

        }
    }
}
