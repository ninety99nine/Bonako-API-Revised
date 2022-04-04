<?php

namespace App\Exceptions;

use Exception;

class CartAlreadyConvertedException extends Exception
{
    protected $message = 'The shopping cart has already been converted to an order';

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
