<?php

namespace App\Exceptions;

use Exception;

class InvalidPaginateException extends Exception
{
    protected $message = 'The paginate value must be true or false to decide whether to paginate the results';

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
