<?php

namespace App\Casts;

use App\Traits\Base\BaseTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Money implements CastsAttributes
{
    use BaseTrait;

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
        return $this->convertToMoneyFormat($value, $attributes['currency']);
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

            //  If we have the array amount value
            if( isset($value['amount']) && !empty($value['amount']) ) {

                return $value['amount'];

            //  If we have the array amount value
            }elseif( isset($value['money']) && !empty($value['money']) ) {

                return $value['money'];

            }

        }

        return $value;
    }
}
