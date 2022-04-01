<?php

namespace App\Exceptions;

use Exception;

class LogoutOfSuperAdminRestrictedException extends Exception
{
    protected $message = 'You do not have permissions to logout this Super Admin';
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
