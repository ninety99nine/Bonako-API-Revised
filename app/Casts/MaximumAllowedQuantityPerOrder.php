<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MaximumAllowedQuantityPerOrder implements CastsAttributes
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
        $description = ('Limit to ' .$value. ($value == 1 ? ' item' : ' items') . ' per order') .
            (strtolower($attributes['allowed_quantity_per_order']) == 'unlimited' ? ' (Not applicable since the allowed quantity per order is unlimited)' : '');

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
