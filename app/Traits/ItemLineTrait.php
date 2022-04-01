<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;

trait ItemLineTrait
{
    use BaseTrait;

    /**
     *  Record the detected change on this item line
     */
    public function recordDetectedChange($changeType, $message = null, $existingProductLine = null)
    {
        /**
         *  Check if the user has already been notified about this detected change.
         *  If the existing item line is Null, or the existing item line is present
         *  but already has a detected change that matches the current change then
         *  the user has already been notified otherwise they have not been
         *  notified.
         */
        $notifiedUser = ($existingProductLine === null) ? false : $existingProductLine->hasDetectedChange($changeType);

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
     *  Set the product line as cancelled
     */
    public function cancelItemLine($cancellationReason = null)
    {
        $this->is_cancelled = true;

        $this->cancellation_reasons = collect($this->cancellation_reasons)->push($cancellationReason)->all();

        return $this;
    }



}
