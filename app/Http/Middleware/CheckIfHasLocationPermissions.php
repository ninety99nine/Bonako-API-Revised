<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckIfHasLocationPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        //  If we have the location via the request
        if( $request->location ) {

            $location = $request->location;

        //  If we have the product via the request
        }elseif( $request->product ) {

            //  Get the product location
            $location = $request->product->location;

        }else{

            $location = null;

        }


        if( $location ){

            if( request()->user()->hasLocationPermissionTo($location, $permission) ) {

                return $next($request);

            }

            throw new AccessDeniedHttpException;

        }

        throw new Exception('This route does not contain the location id required to check permissions.', 400);

    }
}
