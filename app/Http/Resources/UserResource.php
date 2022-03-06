<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class UserResource extends BaseResource
{
    private $viewAsGuest;

    protected $customExcludeFields = ['password'];

    public function __construct($resource, $viewAsGuest = false)
    {
        $this->viewAsGuest = $viewAsGuest;

        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /**
         *  View as Guest
         *
         *  This means we want to limit the amount of information
         *  that we must share e.g Usually we just want to check
         *  if the account exists, so we only limit to the
         *  user name(s) and account status.
         */
        if( $this->viewAsGuest ) {

            //  Don't show links
            $this->showLinks = false;

            //  Overide and apply custom fields
            $this->customFields = ['first_name', 'last_name'];

            //  Overide and apply custom attributes
            $this->customAttributes = ['requires_password', 'requires_mobile_number_verification'];

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        $this->resourceLinks = [

            new ResourceLink('self', route('auth.user.profile'), 'The current authenticated user'),

        ];
    }
}
