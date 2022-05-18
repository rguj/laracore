<?php

namespace Rguj\Laracore\Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Exception;
use Rguj\Laracore\Library\HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Guard;


/**
 * The BaseController supplemented by `HttpResponse`
 * 
 * - Extend this base controller to every child controller (e.g. `class ChildController extends Controller`)
 * - You can define `construct()` on the child class that functions the same as `__construct()`
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public bool $parentConstructed = false;

    /** 
     * `HttpResponse` - the BaseController wrapper
     * 
     * @var Rguj\Laracore\Library\HttpResponse $hr
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

    }


    final public static function __callNonStatic($method, $parameters)
    {
        return (new (STATIC::class))->$method(...$parameters);
    }


    final public function __invokeControllerMethod(string $method, array $args = [], string $class = '', bool $strict = false)
    {
        $ret = null;
        $class = empty($class) ? $this::class : $class;
        if(!class_exists($class))
            throw new exception('Class doesn\'t exists: '. $class);
        if(!method_exists($class, $method))
            throw new Exception('Method `'.$method.'` doesn\'t exists in `'.$class.'`');
        
        // reflect method information
        $ref = new \ReflectionMethod($class, $method);
        $params = $ref->getParameters();
        if(empty($params)) goto point1;

        $firstParam = $params[0];
        list($name, $type) = [$firstParam->getName(), $firstParam->getType()->getName()];

        // check parent, skip if parents are built-in
        $parents = class_parents($type);
        $requiredParent = 'App\Http\Requests\Request';
        if(!array_key_exists($requiredParent, $parents)) {
            if($strict) throw new exception('Required class parent: '.$requiredParent);
            goto point1;
        }

        // remove first arg if its Request
        $firstArg = $args[0] ?? null;
        if(is_object($firstArg) && array_key_exists('Symfony\Component\HttpFoundation\Request', class_parents($firstArg))) {
            array_shift($args);
        }

        // insert request object
        $req = resolve($type);
        array_unshift($args, $req);

        point1:
        $ret = (new $class)->$method(...$args);
        return $ret;
    }


}
