<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Exceptions\InvalidApiHeaderException;

class RequireApiHeaders
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
        //  Check if the request accepts json
        if( $request->acceptsJson() == false ) {
            throw new InvalidApiHeaderException('Include the [Accept: application/json] as part of your request header before consuming this API');
        }

        //  Check if the request content-type is json or form
        if( !in_array($request->getContentType(), ['json', 'form']) ) {
            throw new InvalidApiHeaderException('Include the [Content-Type: application/json] as part of your request header before consuming this API');
        }

        //  Continue
        return $next($request);
    }
}
