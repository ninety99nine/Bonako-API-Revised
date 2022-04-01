<?php

namespace App\Exceptions;

use Exception;

class TeamMemberInvitationAlreadyDeclinedException extends Exception
{
    protected $message = 'This invitation has already been declined';

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
