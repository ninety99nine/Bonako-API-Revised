<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;

trait ItemLineTrait
{
    use BaseTrait;

    /**
     *  Record the detected change on this item line
     */
    public function recordDetectedChange($changeType, $message = null, $existingItemLine = null)
    {
        /**
         *  Check if the user has already been notified about this detected change.
         *  If the existing item line is Null, or the existing item line is present
         *  but already has a detected change that matches the current change then
         *  the user has already been notified otherwise they have not been
         *  notified.
         */
        $notifiedUser = ($existingItemLine === null) ? false : $existingItemLine->hasDetectedChange($changeType);

        $this->detected_changes = collect($this->detected_changes)->push([
            'type' => $changeType,
            'message' => $message,
            'notified_user' => $notifiedUser
        ])->all();

        return $this;
    }

    /**
     *  Return true / false whether the given change type
     *  exists on the item line detected changes
     *
     *  @param string $changeType
     */
    public function hasDetectedChange($changeType)
    {
        return collect($this->detected_changes)->contains(function($detectedChange) use ($changeType){
            return ($detectedChange['type'] == $changeType);
        });
    }

    /**
     *  Empty the detected changes
     */
    public function clearDetectedChanges()
    {
        $this->detected_changes = [];
        return $this;
    }

    /**
     *  Empty the cancellation reasons
     */
    public function clearCancellationReasons()
    {
        $this->cancellation_reasons = [];
        return $this;
    }

    /**
     *  Set the item line as cancelled
     */
    public function cancelItemLine($cancellationReason = null)
    {
        $this->is_cancelled = true;

        if( is_string($cancellationReason) ){
            $cancellationReason = [ $cancellationReason ];
        }elseif( is_null($cancellationReason) ){
            $cancellationReason = [];
        }

        $this->cancellation_reasons = collect($this->cancellation_reasons)->push(...$cancellationReason)->all();

        return $this;
    }

    /**
     *  Prepare the item line for insertion into the database
     */
    public function readyForDatabase($cartId, $convertToJson = true)
    {
        //  Set the cart id
        $this->cart_id = $cartId;

        /**
         *  Convert the specified coupon line to array. This is because we
         *  don't want the casting functionality of the CouponLine Model
         *  e.g To avoid automatic casting to array or vice-versa.
         */
        $output = $this->toArray();

        /**
         *  Foreach of the item line attributes, convert the value to a JSON representation
         *  of itself in the case that the value is an array. This is so that we can insert
         *  the value into the database without the "Array to string conversion" error
         *  especially when using Illuminate\Support\Facades\DB
         *
         *  Sometimes however we may not need to do this especially if we are updating an
         *  existing Model that already implements the cast to "array" feature, since that
         *  will cause double casting which is not desired. Laravel does not automatically
         *  check if the value is a string or an array before converting to Json. It should
         *  only convert an array to string, but sometimes when it receives a string it will
         *  process the string causing unwanted results. Because of this you can conviniently
         *  indicate whether to convert to JSON or not.
         */
        if( $convertToJson ) {

            foreach($output as $attributeName => $attributeValue) {

                //  If this attribute value is a type of array
                if( is_array( $attributeValue ) ) {

                    //  Convert this value to a JSON representation of itself
                    $output[$attributeName] = json_encode($attributeValue);

                }

            }

        }

        return $output;
    }

}
