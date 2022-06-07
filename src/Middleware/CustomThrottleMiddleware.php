<?php

namespace Rguj\Laracore\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Rguj\Laracore\Middleware\ClientInstanceMiddleware;

class CustomThrottleMiddleware extends ThrottleRequests
{

    public array $global_throttle;

    public function __construct()
    {
        $global_throttle = ClientInstanceMiddleware::GLOBAL_THROTTLE;
        dd($global_throttle);
    }

    
    protected function resolveRequestSignature($request)
    {
        //

    }
}
