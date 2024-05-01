<?php

namespace Rguj\Laracore\Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use Exception;
use Rguj\Laracore\Library\HttpResponse;


/**
 * The BaseController supplemented by `HttpResponse`
 *
 * - Extend this base controller to every child controller (e.g. `class ChildController extends Controller`)
 * - You can define `construct()` on the child class that functions the same as `__construct()`
 */
class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public bool $parentConstructed = false;

    /**
     * `HttpResponse` - the BaseController wrapper
     *
     * @var \Rguj\Laracore\Library\HttpResponse $hr
     */
    public $hr;

    /**
     * `final public function __construct()` of the BaseController
     *
     * - This must be a `final public function` to avoid method inheritance of the children
     * - This must not be inherited by the children
     *
     * @requires `Rguj\Laracore\Library\HttpResponse`
     * @return void
     */
    final public function __construct()
    {
        if($this->parentConstructed) goto point1;
        $this->middleware(function ($request, $next) {
            $this->parentConstructed = true;
            $this->hr = new HttpResponse($this, request());  // http response
            $this->hr->__init();

            return $next($request);
        });
        point1:


    }

    /**
     * The construct method for child class
     *
     * @return void
     */
    public function construct()
    {
        // dd(4242);
    }


    final public static function __callNonStatic(string $method, array $parameters)
    {
        // return (new (STATIC::class))->$method(...$parameters);
        return class_method_unstatic(STATIC::class, $method, $parameters);
    }

    final public function __invokeClassMethod(string $method, array $args = [], string $resolveClass = '', string $class = '', bool $strict = false)
    {
        $class = empty($class) ? $this::class : $class;
        return class_controller_method($class, $method, $args, $resolveClass, $strict);
    }

}
