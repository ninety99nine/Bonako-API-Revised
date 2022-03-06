<?php

namespace App\Exceptions;

use Exception;

class AcceptTermsAndConditionsException extends Exception
{
    protected $message = 'Please accept the terms and conditions first';

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return response(['message' => $this->message], 403);
    }
}
