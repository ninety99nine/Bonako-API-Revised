<?php

namespace App\Http\Resources\Helpers;

class ResourceLink {

    public $name;
    public $href;
    public $description;

    public function __construct($name, $href, $description = '')
    {
        $this->name = $name;
        $this->href = $href;
        $this->description = $description;
    }

    /**
     *  Return the link structure for
     *  external API consumption
     */
    public function getLink()
    {
        return [
            $this->name => [
                'href' => $this->href,
                'description' => $this->description
            ]
        ];
    }
}
