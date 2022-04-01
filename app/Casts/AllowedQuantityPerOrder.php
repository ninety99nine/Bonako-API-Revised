<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AllowedQuantityPerOrder implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        $description = ($value == 'limited')
                        ? 'Limited quantity per order (Maximum is '.$attributes['maximum_allowed_quantity_per_order'].($value == 1 ? ' item' : ' items').' per order)'
                        : 'Unlimited quantity per order';

        return [
            'value' => $value,
            'description' => $description
        ];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if( is_array($value) ){

            //  If we have the array value
            if( isset($value['value']) && is_int($value['value']) ) {

                return $value['value'];

            }

        }

        return $value;
    }
}
