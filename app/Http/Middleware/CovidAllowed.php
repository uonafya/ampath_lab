<?php

namespace App\Http\Middleware;

use Closure;

class CovidAllowed
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
        if(in_array(env('APP_LAB'), [5,6]) && auth()->user()->user_type_id && !auth()->user()->covid_allowed) abort(403);        
        return $next($request);
    }
}
