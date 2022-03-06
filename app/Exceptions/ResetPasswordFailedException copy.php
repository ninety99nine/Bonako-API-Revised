<?php

namespace App\Exceptions;

use Exception;

class ResetPasswordFailedException extends Exception
{
    protected $message = 'Failed to reset the account password.';

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
