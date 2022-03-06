<?php

namespace App\Exceptions;

use Exception;

class InvalidPerPageException extends Exception
{
    protected $message = 'The per page value must be a valid number in order to limit the results';

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
