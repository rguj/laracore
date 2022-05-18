<?php

namespace Rguj\Laracore\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class CustomThrottleMiddleware extends ThrottleRequests
{

    public array $global_throttle;

    public function __construct()
    {
        $global_throttle = \App\Http\Middleware\General\ClientInstanceMiddleware::GLOBAL_THROTTLE;
        dd($global_throttle);
    }

    
    protected function resolveRequestSignature($request)
    {
        //

    }
}
