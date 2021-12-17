<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class LocationCheck

{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*if((Session::has('usercurrloc') != '') || (Session::has('usercurrloc') != null)){*/
            return $next($request);
          /*}
            return redirect('/')->with('error','Select your current location');*/
     
    }
} 
