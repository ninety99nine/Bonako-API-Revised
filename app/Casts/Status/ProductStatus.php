<?php

namespace App\Casts\Status;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ProductStatus implements CastsAttributes
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
        $name = 'product';

        switch ($key) {
            case 'is_free':
                $name = $value ? 'Free' : 'Not Free';
                $description = 'This '.$name.' '.($value ? 'is' : 'is not').' free';
                break;
            case 'visible':
                $name = $value ? 'Visible' : 'Hidden';
                $description = 'This '.$name.' '.($value ? 'is' : 'is not').' publicly visible to customers';
                break;
            case 'allow_variations':
                $name = $value ? 'Yes' : 'No';
                $description = 'This '.$name.' '.($value ? 'supports' : 'does not support').' variations (different versions of itself)';
                break;
            case 'show_description':
                $name = $value ? 'Yes' : 'No';
                $description = 'This '.$name.' '.($value ? 'has' : 'does not have').' a description to show to customers';
                break;
            default:
                //  In the case of no match, then return the value as is
                return $value;
                break;
        }

        return [
            'name' => $name,
            'status' => $value ? true : false,
            'description' => $description,
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

            return (in_array($value['status'], ['true', true, '1', 1]) ? 1 : 0);

        }else{

            return (in_array($value, ['true', true, '1', 1]) ? 1 : 0);

        }
    }
}
