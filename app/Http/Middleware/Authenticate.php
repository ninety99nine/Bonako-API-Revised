<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        /**
         *  Since we are designing an API, we do not want to redirect to the
         *  login page when we are not authenticated especially incase a
         *  developer forgets to provide the following headers:
         *
         *  Accept: application/json
         *  Content-Type: application/json
         *
         *  This redirect is a normal behaviour by Laravel when designing non SPA
         *  applications but is not desired for our use case.
         */
        if ( !$request->expectsJson() ) {

            //  return route('login');

        }
    }
}
