<?php

namespace App\Http\Resources;

use \App\Models\Store;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $fields = [...resolve(Store::class)->getFillable(), 'created_at', 'updated_at'];

        return collect($fields)->map(function($field){
            return [$field => $this->$field];
        })->collapse();
    }
}
