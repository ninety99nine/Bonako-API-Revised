<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Api\Ussd\UssdService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckIfRequestFromUssdServer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         *  Check if the request is coming from the USSD server
         */
        if( resolve(UssdService::class)->verifyIfRequestFromUssdServer() ){

            return $next($request);

        }

        //  Deny access
        throw new AccessDeniedHttpException;
    }
}
