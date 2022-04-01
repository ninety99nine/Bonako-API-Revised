<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     *  Check if this user is a Super Admin
     */
    protected $isSuperAdmin;

    /**
     *  Check if this user is an Authourized User
     */
    protected $isAuthourizedUser;

    /**
     *  Check if this user is an Public User
     */
    protected $isPublicUser;

    /**
     *  Fields to overide the default provided fillable fields of the model
     */
    protected $customFields = null;

    /**
     *  Fields to add to be part of the final transformed fields
     */
    protected $customIncludeFields = null;

    /**
     *  Fields to remove from being part of the final transformed fields
     */
    protected $customExcludeFields = null;

    /**
     *  Attributes to overide the default provided appends of the model
     */
    protected $customAttributes = null;

    /**
     *  Attributes to add to be part of the final transformed attributes
     */
    protected $customIncludeAttributes = null;

    /**
     *  Attributes to remove from being part of the final transformed attributes
     */
    protected $customExcludeAttributes = null;

    /**
     *  The resource links
     */
    protected $resourceLinks = [];

    /**
     *  Whether to show the attributes payload
     */
    protected $showAttributes = true;

    /**
     *  Whether to show the links payload
     */
    protected $showLinks = true;

    public function __construct($resource)
    {
        /**
         *  Run the normal Laravel JsonResource __construct() method
         *  so that Laravel can do the usual procedure before we
         *  run our additional logic.
         */
        parent::__construct($resource);

        /**
         *  If this action is performed by a user to their own profile,
         *  then this is considered an authourized user
         *
         *  The following is checking if this action was performed
         *  under the route names:
         *
         *  "auth.login", "auth.register", "auth.login", "auth.reset.password", e.t.c
         */
        $this->isAuthourizedUser = request()->routeIs('auth.*');
        $this->isSuperAdmin = ($user = request()->user()) ? $user->isSuperAdmin() : false;
        $this->isPublicUser = !($this->isAuthourizedUser || $this->isSuperAdmin);


        //  If the request does not intend to disable casting completely
        if( !in_array(request()->input('_noCasting'), [true, 'true', '1'], true) ) {

            /**
             *  Cast the fields of this resource
             *
             *  Apply the temporary cast at runtime using the mergeCasts method.
             *  These cast definitions will be added to any of the casts already
             *  defined on the model
             *
             *  Refer to: https://laravel.com/docs/8.x/eloquent-mutators
             */
            $this->mergeCasts($this->getTranformableCasts());

        }
    }

    /**
     *  Transform the resource into an array.
     */
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    /**
     *  Transform the resource into an array from transformable
     *  fields and attributes of the model instance
     */
    public function transformedStructure()
    {
        //  Set the transformable model fields
        $data = $this->extractFields();

        //  If the request does not intend to hide the attributes completely
        if( !in_array(request()->input('_noAttributes'), [true, 'true', '1'], true) ) {

            //  Set the transformable model attributes (If permitted)
            $data['_attributes'] = $this->showAttributes ? $this->extractAttributes() : [];

        }


        //  If the request does not intend to hide the links completely
        if( !in_array(request()->input('_noLinks'), [true, 'true', '1'], true) ) {

            //  Set the transformable model links (If permitted)
            $data['_links'] = $this->showLinks ? $this->getLinks() : [];

        }

        return $data;
    }

    /**
     *  Return the transformable model fields
     */
    public function extractFields()
    {
        //  Get model fields (Method defined in the App\Models\Traits\BaseTrait)
        $fields = $this->customFields === null ? $this->getTransformableFields() : $this->customFields;

        //  Include additional custom fields
        $fields = $this->customIncludeFields === null ? $fields : collect($fields)->merge($this->customIncludeFields)->toArray();

        //  Exclude additional custom fields
        $fields = $this->customExcludeFields === null ? $fields : collect($fields)->filter(fn($field) => !in_array($field, $this->customExcludeFields))->toArray();

        //  If the request intends to specify specific fields to show
        if( request()->filled('_includeFields') == true ) {

            //  Capture the fields that we must exclude
            $fieldsToExclude = Str::of( Str::replace(' ', '', request()->input('_includeFields')) )->explode(',')->toArray();

            $fields = collect($fields)->filter(fn($field) => in_array($field, $fieldsToExclude))->toArray();

        //  If the request intends to specify specific fields to exclude
        }elseif( request()->filled('_excludeFields') == true ) {

            //  Capture the fields that we must exclude
            $fieldsToExclude = Str::of( Str::replace(' ', '', request()->input('_excludeFields')) )->explode(',')->toArray();

            $fields = collect($fields)->filter(fn($field) => !in_array($field, $fieldsToExclude))->toArray();

        }

        //  Return the fields as key-value pairs
        return collect($fields)->map(fn($field) => [$field => $this->$field])->collapse();
    }

    /**
     *  Return the transformable model attributes
     */
    public function extractAttributes()
    {
        //  Get model attributes (Method defined in the App\Models\Traits\BaseTrait)
        $attributes = $this->customAttributes === null ? $this->getTransformableAppends() : $this->customAttributes;

        //  Include additional custom attributes
        $attributes = $this->customIncludeAttributes === null ? $attributes : collect($attributes)->merge($this->customIncludeAttributes)->toArray();

        //  Exclude additional custom attributes
        $attributes = $this->customExcludeAttributes === null ? $attributes : collect($attributes)->filter(fn($field) => !in_array($field, $this->customExcludeAttributes))->toArray();

        //  If the request intends to specify specific attributes to show
        if( request()->filled('_includeAttributes') == true ) {

            //  Capture the attributes that we must exclude
            $attributesToExclude = Str::of( Str::replace(' ', '', request()->input('_includeAttributes')) )->explode(',')->toArray();

            $attributes = collect($attributes)->filter(fn($attribute) => in_array($attribute, $attributesToExclude))->toArray();

        //  If the request intends to specify specific attributes to exclude
        }elseif( request()->filled('_excludeAttributes') == true ) {

            //  Capture the attributes that we must exclude
            $attributesToExclude = Str::of( Str::replace(' ', '', request()->input('_excludeAttributes')) )->explode(',')->toArray();

            $attributes = collect($attributes)->filter(fn($attribute) => !in_array($attribute, $attributesToExclude))->toArray();

        }

        //  Return the attributes as key-value pairs
        return collect($attributes)->map(fn($attribute) => [$attribute => $this->$attribute])->collapse();
    }

    /**
     *  Overide to provide the links
     */
    public function setLinks()
    {
        $this->resourceLinks = [];
    }

    /**
     *  Return the transformable model links
     *  Overide to provide the links
     */
    public function getLinks()
    {
        $this->setLinks();

        return collect($this->resourceLinks)->map(fn($resourceLink) => $resourceLink->getLink())->collapse();
    }
}
