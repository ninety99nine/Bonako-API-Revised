<?php

namespace App\Exceptions;

use Exception;

class CartRequiresStoreException extends Exception
{
    protected $message = 'The shopping cart store was not found';
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
