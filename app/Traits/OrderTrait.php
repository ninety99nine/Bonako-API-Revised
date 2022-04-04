<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;

trait OrderTrait
{
    use BaseTrait;

    /**
     *  Generate the delivery confirmation code for this order
     */
    public function generateConfirmationCode()
    {
        //  By default we assume that the delivery confirmation code exists
        $codeExists = true;

        //  Get the confirmation codes used on the customers previous orders
        $existingConfirmationCodes = $this->customer->orders()->whereNotNull('delivery_confirmation_code')->pluck('delivery_confirmation_code');

        while($codeExists == true) {

            //  Generate a random number
            $randomNumber = mt_rand(1, 999999);

            //  Pad with leading "0" characters
            $randomCode = str_pad($randomNumber, 6, 0, STR_PAD_RIGHT);

            //  Check if the code is currently in use
            $codeExists = collect($existingConfirmationCodes)->contains($randomCode);

        }

        //  Set this code as the delivery confirmation code
        $this->delivery_confirmation_code = $randomCode;

        //  Return the current Model instance
        return $this;
    }
}
