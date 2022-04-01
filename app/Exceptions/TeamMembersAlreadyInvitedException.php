<?php

namespace App\Exceptions;

use Exception;

class TeamMembersAlreadyInvitedException extends Exception
{
    protected $message = 'The team members have already been invited';

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
