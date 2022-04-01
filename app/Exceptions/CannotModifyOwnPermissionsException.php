<?php

namespace App\Exceptions;

use Exception;

class CannotModifyOwnPermissionsException extends Exception
{
    protected $message = 'You are not allowed to modify your own permissions';

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
