<?php

namespace App\Exceptions;

use Exception;

class LocationRoleDoesNotExistException extends Exception
{
    protected $message = 'The specified location role does not exist';
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
