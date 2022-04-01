<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class UserResource extends BaseResource
{
    protected $isProfileOwner;
    protected $checkingAccountExistence;
    protected $customExcludeFields = ['password'];

    /**
     *  When iterating over a collection, the constructor will receive the
     *  resource as the first parameter and then the index number as the
     *  second parameter. Note that the index is provided only if this
     *  resource is part of a resource collection, otherwise we
     *  default to null.
     */
    public function __construct($resource, $collectionIndex = null, $checkingAccountExistence = false)
    {
        $this->checkingAccountExistence = $checkingAccountExistence;

        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /**
         *  Checking account existence
         *
         *  If we are checking the account existence then
         *  limit the information we share.
         */
        if( $this->checkingAccountExistence ){

            //  Don't show links
            $this->showLinks = false;

            //  Overide and apply custom fields
            $this->customFields = ['first_name', 'last_name', 'mobile_number'];

            //  Overide and apply custom attributes
            $this->customAttributes = ['name', 'requires_password', 'requires_mobile_number_verification'];

        /**
         *  Viewing as Public User
         *
         *  If we are veiwing as the general public then limit the information
         *  we share. Usually we just want to check if the account exists,
         *  so we only limit to the user name(s) and account status.
         */
        }elseif( $this->isPublicUser ) {

            //  Overide and apply custom fields
            $this->customFields = ['first_name', 'last_name', 'mobile_number'];

            //  Overide and apply custom attributes
            $this->customAttributes = ['name'];

        }

        /**
         *  If the user is accessed via a location relationship, we can gain access
         *  to the location-user pivot information. This information is conveniently
         *  stored as part of our User Model via the "location_association" appended
         *  property. If this property is empty then we can exclude it from the
         *  payload sicne it serves no purpose e.g If we are simply accessing
         *  a user directly instead of a location relationship.
         */
        if( empty($this->resource->location_association) ) {

            //  Exclude the location association from the payload
            $this->customExcludeAttributes = array_merge(
                ($this->customExcludeAttributes ?? []), ['location_association']
            );

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        //  Links for the Public User
        if( $this->isPublicUser ) {

            $this->resourceLinks = [

                new ResourceLink('self', route('users.show', ['user' => $this->resource->id]), 'The public user profile'),

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
                new ResourceLink('self', route($routeNamePrefix.'profile.show', $this->isAuthourizedUser ? [] : ['user' => $this->resource->id]), 'The private user profile')
            ];

        }
    }
}
