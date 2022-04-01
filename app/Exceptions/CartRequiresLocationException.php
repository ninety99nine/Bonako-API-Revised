<?php

namespace App\Exceptions;

use Exception;

class CartRequiresLocationException extends Exception
{
    protected $message = 'The shopping cart location was not found';
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return response(['message' => $this->message], 400);
    }
}
