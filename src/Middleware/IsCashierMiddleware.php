<?php

namespace Rguj\Laracore\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsCashierMiddleware
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
        if(!auth()->check() || !cuser_is_cashier()) {
            abort(401);
        }
        
        return $next($request);
    }
}
