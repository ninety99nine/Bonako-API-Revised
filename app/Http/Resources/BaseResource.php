<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     *  Fields to overide the default provided fillable fields of the model
     */
    protected $customFields = [];

    /**
     *  Fields to add to be part of the final transformed fields
     */
    protected $customIncludeFields = [];

    /**
     *  Fields to remove from being part of the final transformed fields
     */
    protected $customExcludeFields = [];

    /**
     *  Attributes to overide the default provided appends of the model
     */
    protected $customAttributes = [];

    /**
     *  Attributes to add to be part of the final transformed attributes
     */
    protected $customIncludeAttributes = [];

    /**
     *  Attributes to remove from being part of the final transformed attributes
     */
    protected $customExcludeAttributes = [];

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

        //  Set the transformable model attributes (If permitted)
        $data['_attributes'] = $this->showAttributes ? $this->extractAttributes() : [];

        //  Set the transformable model links (If permitted)
        $data['_links'] = $this->showLinks ? $this->getLinks() : [];

        return $data;
    }

    /**
     *  Return the transformable model fields
     */
    public function extractFields()
    {
        //  Get model fields (Method defined in the App\Models\Traits\BaseTrait)
        $fields = !empty($this->customFields) ? $this->customFields : $this->getTransformableFields();

        //  Include additional custom fields
        $fields = collect($fields)->merge($this->customIncludeFields)->toArray();

        //  Exclude additional custom fields
        $fields = collect($fields)->filter(fn($field) => !in_array($field, $this->customExcludeFields))->toArray();

        //  Return the fields as key-value pairs
        return collect($fields)->map(fn($field) => [$field => $this->$field])->collapse();
    }

    /**
     *  Return the transformable model attributes
     */
    public function extractAttributes()
    {
        //  Get model attributes (Method defined in the App\Models\Traits\BaseTrait)
        $attributes = !empty($this->customAttributes) ? $this->customAttributes : $this->getTransformableAppends();

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

        return collect($this->resourceLinks)->map(fn($resourceLink) => $resourceLink->getLink());
    }
}
