<?php

namespace Rguj\Laracore\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsCStaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(!auth()->check() || !cuser_is_cstaff()) {
            abort(401);
        }
        
        return $next($request);
    }
}
