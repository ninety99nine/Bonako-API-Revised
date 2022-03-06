<?php

namespace App\Exceptions;

use Exception;

class InvalidSortingException extends Exception
{
    protected $message = 'The sorting applied is not valid';

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
