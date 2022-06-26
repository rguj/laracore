<?php 

namespace Rguj\Laracore\Library;

// ----------------------------------------------------------
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use Rguj\Laracore\Middleware\ClientInstanceMiddleware as CLIENT_INSTANCE;

use App\Core\Adapters\Theme;
use App\Http\Controllers\IndexController;
use App\Providers\AppServiceProvider;
use App\Models\Role;
use App\Models\Permission;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\CLHF;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\StorageAccess;
use Rguj\Laracore\Library\WebClient;
use Illuminate\Support\ViewErrorBag;
use Rguj\Laracore\Request\Request as BaseRequest;

// ----------------------------------------------------------

/**
 * Wrapper for BaseController with vast function helpers
 * 
 * - Please create a `final public function __construct() {}` in BaseController
 * 
 * @author rguj <slimyslime777@gmail.com>
 * @version 1.0.0
 * @throws Exception
 */
class HttpResponse {

    private $request;
    private $class;
    private $reflection;
    private $reflectionParent;
    private $reflectionIndex;
    private array $methods = [];
    private array $methodsParent = [];
    private string $routeName;
    private array $routeNames;
    private array $constants;
    private array $constantsIndex;

    private bool $isThemeLRPM = false;
    private bool $isDevMode = false;
    
    private bool $isSuccess = false;
    private array $url = [false, false, ''];  // [ noActionOrLoad, isHrefOrReplace, url ]
    private bool $isAjax = false;
    private string $title = '';
    private string $desc = '';
    private string $method;
    private float $processTime = 0.00;
    private string $defaultPurposeKey = '_purpose';

    private array $permissions = [];  // gate permissions name
    private $dbPermissions = [];  // db permissions name
    private bool $noPermission = false;
    private int $userId = 0;
    private bool $isUserAdmin = false;
    private bool $hasInit = false;
    private int $logicTriggered = 0;
    private array $logicData = [false, '', null];  // [ hasLogic, err_msg, data ]

    private array $notifications = [];
    private array $purposes = [];
    private array $alertsToastr = [];
    private array $alertsSwal = [];
    private array $formRules = [];
    private array $formPreloads = [];
    private array $formValues = [];
    private array $formErrors = [];
    private array $data = [];
    private array $rootData = [];

    private array $alertTypes = ['swal2', 'toastr'];
    private array $alertSwalCategories = ['success', 'info', 'question', 'warning', 'error'];
    private array $alertToastrCategories = ['success', 'info', 'warning', 'error'];





    /**
     * Constructs HttpResponse object
     *
     * @param \Illuminate\Http\Request|\Illuminate\Foundation\Http\FormRequest $request
     * @param string $class_name
     */
    // public function __construct($request, string $class_name)
    public function __construct($class, $request)
    {
        // validate $request    
        $request_class_check = (get_class($request) === "Illuminate\Http\Request" || get_parent_class($request) === "Illuminate\Foundation\Http\FormRequest");
        if(!$request_class_check)
            throw new Exception('`$request` must be an instance of Request or FormRequest');        
        if(!method_exists($request, 'ajax'))
            throw new Exception('Method `ajax()` doesn\'t exists');

        // validate $class_name
        $parent_class = 'App\Http\Controllers\Controller';
        // dd(get_parent_class($class));
        if(get_parent_class($class) !== $parent_class) {
            throw new exception('Parent class must be '.$parent_class);
        }

        // get class and request
        $this->class = $class;
        $this->request = $request;

        // check if base controller was constructed
        if(!$class->parentConstructed) {
            throw new exception('BaseController was not constructed');
        }

        // get reflection and methods
        list($this->reflection, $this->methods) = $this->__getReflectionAndExclusiveMethods($class);
        list($this->reflectionParent, $this->methodsParent) = $this->__getReflectionAndExclusiveMethods(get_parent_class($class));
        $this->constants = $this->reflection->getConstants() ?? [];
        $this->constantsIndex = (new \ReflectionClass(\App\Http\Controllers\IndexController::class))->getConstants() ?? [];

        // invoke the custom child `construct()`
        // if(method_exists($this->class, 'construct')) {
        //     $this->class->construct();
        // }

        // initialize
        // $this->init();
    }






    /**
     * Get the class' Reflection object and its exclusive methods
     *
     * @param object|string $class
     * @return array<int,\ReflectionClass|array>
     */
    public function __getReflectionAndExclusiveMethods($class)
    {        
        $class_name = is_string($class) ? $class : $class::class;
        $ref = new \ReflectionClass($class);
        $o = [];
        foreach(obj_reflect($ref->getMethods(), true) as $k=>$v) {
            if($v['class'] === $class_name) {                
                $o[] = $v['name'];
            }
        }
        return [$ref, $o];
    }

    protected function __mutatePurposes()
    {
        // get index purposes for merging
        $ref1 = new \ReflectionClass(\App\Http\Controllers\IndexController::class);
        $constants_index = $ref1->getConstants();
        $this->constants = array_merge($constants_index, array_merge($this->constants, $constants_index));
        $purposes = [];
        foreach($this->constants as $key=>$val) {
            if(Str::startsWith($key, 'P_')) {
                $k = Str::replaceFirst('P_', '', $key);
                if(empty($val))
                    throw new Exception('Purpose `'.$k.'` value must not be empty');
                if(!(is_int($val) || is_string($val)))
                    throw new Exception('Purpose `'.$k.'` value must be int or string ('.gettype($val).' provided)');
                $purposes[$k] = $val;
            }
        }
        $purposes = array_change_key_case($purposes, CASE_UPPER);
        return $purposes;
    }

    protected function __getWith(bool $isAjax = false)
    {        
        $d = array_merge($this->rootData, $this->data);
        return $isAjax ? [
            's' => $this->isSuccess,    // is _success
            'm' => [                    // messages
                'toastr' => $this->alertsToastr,
                'swal2'  => $this->alertsSwal,
            ],
            'e' => $this->formErrors,   // errors
            'r' => $this->url,          // redirect url
            'd' => $d,                  // data
            'p' => $this->processTime,  // process time
        ] : [
            'page' => [
                'title'            => $this->title,
                'desc'             => $this->desc,
                'notifications'    => $this->notifications,
                'alerts' => [
                    'categories' => [
                        'swal2'    => $this->alertSwalCategories,
                        'toastr'   => $this->alertToastrCategories,
                    ],
                    'toastr'       => $this->alertsToastr,
                    'swal2'        => $this->alertsSwal,
                ],
                'process_time'     => $this->processTime,
            ],
            'form' => [
                'success'    => $this->isSuccess,
                'rules'      => $this->formRules,
                'preloads'   => $this->formPreloads,
                'values'     => $this->formValues,
                'errors'     => $this->formErrors,
                'purposes'   => $this->purposes,
            ],
            'config' => [
                'is_theme_lrpm'    => $this->isThemeLRPM,
                'is_dev_mode'      => $this->isDevMode,
            ],
            'data'           => $this->data,
        ];
    }

    protected function __evalInit() {
        if(!$this->hasInit)
            throw new Exception(basename(__FILE__, '.php').' hasn\'t been initialized');

        // add route name to permissions array if not empty
        if(!empty($this->routeName) && !in_array($this->routeName, $this->permissions, true)) {
            array_unshift($this->permissions, $this->routeName);
        }

        // get permit status
        $permit_status = 2;
        $has_permission = !$this->noPermission;
        if(!$has_permission) {
            $permit_status = $this->getPermitStatus($this->permissions, cuser_data());
            foreach($this->permissions as $permission) {    
                // flag permission
                if(!$has_permission && !empty($permission))
                    $has_permission = true;                
                // check permission name if exists in db
                if(!Str::startsWith($permission, 'generated::') && !in_array($permission, $this->dbPermissions, true)) {
                    throw new Exception('The defined permission `'.$permission.'` doesn\'t exists');
                }
            }
        }
        // evaluate
        if(!$has_permission)
            abort(501, 'Undefined Permission');  // throw new Exception('No permission was defined');
        if($permit_status <= 0)
            abort(401);
    }

    protected function __evalAlertType(string $alert_type)
    {
        if(!in_array($alert_type, $this->alertTypes, true))
            throw new exception('Invalid alert type: '.$alert_type);
    }

    public function __init(bool $force=false)
    {
        if(!$force && $this->hasInit)
            throw new exception('Failed to re-initialize again');
            
        // invoke the custom child `construct()`
        if(method_exists($this->class, 'construct')) {
            $this->class->construct();
        }
        
        // CREATE & MUTATE PURPOSES
        $purposes = $this->__mutatePurposes();

        // setters
        $this->constants = $this->reflection->getConstants() ?? [];
        $this->dbPermissions = Permission::all('title')->pluck('title')->toArray();
        $this->routeName = str_sanitize($this->request->route()->getName());
        $this->routeNames = route_names(true);
        $this->request = $this->request;
        $this->method = $this->request->method();
        $this->isAjax = $this->request->ajax();
        $this->isDevMode = (bool)config('app.debug', false);
        $this->setPurposes($purposes);
        $this->setResetWith();

        // get session alerts toastr and swal
        $fb = session_get_alerts(true);
        foreach($fb as $k=>$v) {
            if(!array_key_exists('type', $v) || !array_key_exists('status', $v) || !array_key_exists('msg', $v))
                continue;
            $t = $v['title'] ?? '';
            switch($v['type']) {
                case 'swal2':
                    // $this->addAlertSwal($v['status'], $v['msg'], $t, $this->isAjax);
                    $this->addAlertSwal($v['status'], $v['msg'], $t, false);
                    break;
                case 'toastr':
                    // $this->addAlertToastr($v['status'], $v['msg'], $t, $this->isAjax);
                    $this->addAlertToastr($v['status'], $v['msg'], $t, false);
                    break;
            }
        }

        // get user id and privileges
        $this->setUserId(cuser_id() ?? 0);
        $this->setIsUserAdmin(cuser_is_admin());

        // flag init
        $this->hasInit = true;
    }








    
    








    /**
     * Gets the value from the array key
     *
     * @param string $key default `_purpose`
     * @param array $arr `[purpose_key => closure|value]`
     * @param boolean $isEncrypted default `false`
     * @return void
     * @throws Exception
     */
    public function purposeLogic(array $args = [], string $key = '', bool $isEncrypted = false)
    {        
        $this->logicTriggered++;
        $key = empty($key) ? $this->defaultPurposeKey : $key;

        $func1 = function(array $args, string $key, bool $isEncrypted){
            $data = [null, '', null];  // [ null|bool, err_msg, return_data ]
            if(!$this->request->has($key)) {
                return $data;
            }
            $this->hasPurposeLogic = true;
            $this->__evalInit();

            $vld = $this->validateInputPurpose($key, $isEncrypted);
            if(!$vld[0]) {
                $data[0] = false;
                $data[1] = $vld[1];
                if(config('user.is_admin')) throw new exception($data[1]);
                else abort(404);
                return $data;
            }

            // check method name
            $func_name1 = Str::of($vld[2])->headline()->replace(' ', '');
            if(empty($func_name1)) {
                throw new exception('Method name is empty');
            }

            // check if method exists
            $func_name2 = 'logic'.$func_name1;
            $method_exists = method_exists($this->class, $func_name2);
            if(!$method_exists) {
                throw new exception('Method `'.$func_name2.'` doesn\'t exists in '.$this->class::class);
            }

            // eval request arg/s
            if(!empty($args) && is_object($args[0]) && method_exists($this->class, $func_name2)
                && !empty($params = (new \ReflectionMethod($this->class, $func_name2))->getParameters())
            ) {
                $a = get_class($args[0]);
                $b = class_parents($args[0]);
                $b = !array_key_exists($a, $b) ? array_merge([$a=>$a], $b) : $b;
                $c = $params[0]->getType()->getName();
                $d = class_parents($params[0]->getType()->getName());
                if(array_key_exists(BaseRequest::class, $d) && $c !== BaseRequest::class) {
                    $args[0] = resolve($c);
                }
            }
            
            // $data[2] = $this->class->{$func_name2}($this->request);
            $data[2] = $this->class->{$func_name2}(...$args);
            $data[0] = true;
            return $data;
        };

        $this->logicData = $func1($args, $key, $isEncrypted);
    }


    public function resolveRequest(string $requestClass)
    {
        if(!class_exists($requestClass))
            throw new exception('Non-existent class: '.$requestClass);
        if(!array_key_exists(BaseRequest::class, class_parents($requestClass)))
            throw new exception('$requestClass must have a parent class `'.BaseRequest::class.'`');

        /** @var \Rguj\Laracore\Request\Request $obj */
        $obj = resolve($requestClass);

        if($obj->validator->fails()) {
            dd('failed to validate');
            // return $obj->validator->
        }

    }























    




    // ADDERS

    
    public function addAlertAuto(bool $cond, string $alert_type = 'toastr', bool $as_session = false)
    {
        $this->__evalAlertType($alert_type);
        $str = '';
        $type = $cond ? 'success' : 'error';
        $arr = [
            'index' => 'show all',
            'create' => 'show create',
            'store' => 'create',
            'edit' => 'show edit',
            'update' => '',
            'show' => '',
            'destroy' => 'delete',
            'massDestroy' => 'mass delete',
        ];
        $caller_method = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1]['function'] ?? '';
        if(!empty($caller_method) && array_key_exists($caller_method, $arr)) {
            $str = !empty($arr[$caller_method]) ? $arr[$caller_method] : $caller_method;
        }       
        $str = Str::of($str.' '.$type)->trim()->lower()->ucfirst();
        match($alert_type) {
            'toastr' => $this->addAlertToastr($type, $str, $as_session),
            'swal2' => $this->addAlertSwal($type, $str, $as_session),
        };
        point1:
    }

    public function addData(array $data, bool $includeToWith = true)
    {
        if($includeToWith)
            $this->data = array_merge($this->data, $data);
        else
            $this->rootData = array_merge($this->rootData, $data);
    }

    /**
     * Adds gate permission/s
     *
     * @param string|array $permission
     * @return void
     */
    public function addPermission($permission)
    {
        $data = [];
        if(is_array($permission)) {
            foreach($permission as $p) {
                if(!is_string($p))
                    continue;
                $p = str_sanitize($p);
                if(!empty($p))
                    $data[] = $p;
            }
        } else {
            $permission = str_sanitize($permission);
            $data = !empty($permission) ? [$permission] : $data;
        }
        if(!empty($data)) {
            $this->permissions = (array_merge($this->permissions, $data));
        }
        array_unique($this->permissions);
    }

    public function addFieldError(string $key, string $val) 
    {
        $v = $this->validateFieldError($key, $val);
        if(!$v[0]) throw new exception($v[1]);
        $this->formErrors[] = [$key, $val];
    }




    public function __asSessionAlert($as_session = null)
    {
        $a = $as_session;
        if(is_bool($a)) goto point1;
        $m = $this->request->method();
        $j = $this->request->ajax();
        if($j) {
            $a = !in_array($m, ['POST']);
        } else {     
            $a = in_array($m, ['POST']);
        }
        $a = !is_bool($a) ? !$this->isAjax : $a; // DEFECTIVE
        point1:
        return $a;
    }

    /**
     * Adds a sweetalert alert
     *
     * @param string $alert_status `success, info, question, warning, error`
     * @param string $alert_msg
     * @param string $alert_title
     * @param null|bool $as_session
     * @return void
     */
    public function addAlertSwal(string $alert_status, string $alert_msg, string $alert_title = '', $as_session = null)
    {
        $as_session = $this->__asSessionAlert($as_session);

        // dd($this->request);

        if(!in_array($alert_status, $this->alertSwalCategories))
            throw new Exception('Invalid alert type `'.$alert_status.'`');
        if(empty($alert_msg))
            throw new Exception('Alert message must not be empty');
        if($as_session)
            session_push_alert($alert_status, $alert_msg, $alert_title, 'swal2');
        else
            $this->alertsSwal[] = ['status'=>$alert_status, 'msg'=>$alert_msg, 'title'=>$alert_title];
    }

    /**
     * Adds a toastr alert
     *
     * @param string $alert_status `success, info, warning, error`
     * @param string $alert_msg
     * @param string $alert_title
     * @param null|bool $as_session
     * @return void
     */
    public function addAlertToastr(string $alert_status, string $alert_msg, string $alert_title = '', $as_session = null)
    {
        // $as_session = !is_bool($as_session) ? !$this->isAjax : $as_session;
        $as_session = $this->__asSessionAlert($as_session);
        if(!in_array($alert_status, $this->alertToastrCategories))
            throw new Exception('Invalid alert type `'.$alert_status.'`');
        if(empty($alert_msg))
            throw new Exception('Alert message must not be empty');
        if($as_session)
            session_push_alert($alert_status, $alert_msg, $alert_title, 'toastr');        
        else
            $this->alertsToastr[] = ['status'=>$alert_status, 'msg'=>$alert_msg, 'title'=>$alert_title];
    }




















    // SETTERS

    
    protected function setResetWith()
    {
        $this->with = $this->__getWith(false);
    }

    public function setNoPermission()
    {
        $this->noPermission = true;
    }

    public function setPurposes(array $purposes)
    {
        $purposes2 = [];
        if(empty($purposes)) goto point1;
        if(!AppFn::ARRAY_IsTypeAssociative($purposes))
            throw new Exception('Purposes is not a sequential array');
        foreach($purposes as $key=>$val) {
            $key = strtoupper($key);
            $val_e = crypt_sc($val, 0, true);
            if(!$val_e[0])
                throw new exception('Could not encrypt purpose `'.$key.'`');
            if(array_key_exists($val, $purposes2))
                throw new exception('Key already exists [ '.$val.', '.$purposes2[$val][2].', '.$key.' ]');
            $purposes2[$val] = [$val_e[2], '<input name="_purpose" value="'.$val_e[2].'" type="hidden" />', $key];             
        }
        point1:
        $this->purposes = $purposes2;
    }

    public function setIsAjax(bool $isAjax) {
        $this->isAjax = $isAjax;
    }

    public function setIsThemeLRPM(bool $isThemeLRPM) {
        $this->isThemeLRPM = $isThemeLRPM;
    }

    /**
     * Sets URL to which it redirects
     * - do not send a data which gives a reload command, you must specify URL
     *
     * @param Illuminate\Http\RedirectResponse|string $redirectTo
     * @param boolean $isHrefOrReplace
     * @return void
     */
    public function setURL($redirectTo, bool $isHrefOrReplace = true) 
    {
        $isObj = is_object($redirectTo) && get_class($redirectTo) === 'Illuminate\Http\RedirectResponse';
        if(!($isObj || is_string($redirectTo)))
            throw new Exception('$redirectTo must be a string or RedirectResponse object');
        $target_url = (string)($isObj ? $redirectTo->getTargetUrl() : $redirectTo);
        $this->url = [true, $isHrefOrReplace, $target_url];
    }

    public function setTitle(string $title, bool $include_app_name=true, string $separator = ' | ') 
    {
        $this->title = view_title($title, $include_app_name, $separator);
    }

    public function setDesc(string $desc) 
    {
        $this->desc = $desc;
    }
    
    public function setFormRules(array $formRules) 
    {
        $this->formRules = $formRules;
    }

    public function setFormPreloads(array $formPreloads) 
    {
        $this->formPreloads = $formPreloads;
    }

    public function setFormValues(array $formValues) 
    {
        $this->formValues = $formValues;
    }

    public function setFormErrors(array $formErrors, bool $onlyFirstError = false) 
    {
        foreach($formErrors as $key=>$val) {
            if(!$onlyFirstError) {                
                $v = $this->validateFieldError($key, $val, $onlyFirstError);
                if(!$v[0]) throw new exception($v[1]);
            }            
        }
        $this->formErrors = $formErrors;
    }

    public function setData(array $data)
    {
        foreach($data as $key=>$val) {
            if(array_key_exists($key, $this->data) || array_key_exists($key, $this->with))
                throw new Exception('$data array key `'.$key.'` already exists');
        }
        $this->data = $data;
    }
    
    public function setIsSuccess(bool $isSuccess) {
        $this->isSuccess = $isSuccess;
    }

    public function setUserId(int $userId = 0) {
        $this->userId = $userId <= 0 ? 0 : $userId;
    }
    
    public function setIsUserAdmin(bool $isUserAdmin) {
        $this->isUserAdmin = $isUserAdmin;
    }

    /**
     * Sets the end process time
     *
     * @return void
     */
    public function setEndProcessTime()
    {
        $this->processTime = (microtime(true) - LARAVEL_START);
        Config::set('env.PROCESS_TIME', $this->processTime);
    }

















    


















    // GETTERS

    public function getUserID()
    {
        return $this->userId;
    }

    public function getIsUserAdmin()
    {
        return $this->isUserAdmin;
    }

    /**
     * Gets the decrypted input from the request
     *
     * @param string $key
     * @return string
     * @throws Exception
     */
    public function getInputDecrypt(string $key, bool $isEncrypted)
    {
        $vld = $this->validateInputPurpose($key, $isEncrypted);
        if(!$vld[0]) throw new exception($vld[1]);
        return $vld[2];
    }

    public function getPurpose($key, $mode=null)
    {
        if(!(is_null($mode) || (is_int($mode) && in_array($mode, [0, 1]))))
            throw new Exception('$mode must be null or int (0, 1)');
        if(!array_key_exists($key, $this->purposes))
            throw new Exception('Array key `'.$key.'` doesn\'t exists');
        return is_null($mode) ? $this->purposes[$key] : $this->purposes[$key][$mode];
    }
	
	public function getPurposes($mode = null)
    {
        $arr = [];
        foreach($this->purposes as $k=>$v) {
            $p = $this->getPurpose($k, $mode);
            $arr[$k] = $p;
        }
        return $arr;
    }

    public function getWith(bool $withKey = false)
    {
        $with = $this->__getWith($this->isAjax);
        return array_merge($this->rootData, ($withKey ? ['with'=>$with] : $with));
        
    }

    public function getJSON()
    {
        $this->__evalInit();
        return response()->json($this->getWith(false));
    }

    /** 
     * Check if user has permit
     *
     * @param array $permissions
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return int
     */
    public function getPermitStatus(array $permissions, $user)
    {
        if($this->noPermission)
            return 2;

        /** @var \Spatie\Permission\Models\Permission $user */
        $user = cuser_data();
        if(is_null($user)) return 0;
        $has_any = $user->hasAnyPermission($permissions);
        $has_all = $user->hasAllPermissions($permissions);
        $status = $has_all ? 2 : ($has_any ? 1 : 0);
        return $status;
    }

    public function getNoContent()
    {
        return response()->noContent();
    }

    /**
     * Returns `View` or `JsonResponse`
     * 
     * - the `$view` will not be used if the request is AJAX, instead you must specify it using the method `setURL()`
     *
     * @param \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|string $view
     * @param boolean $isSuccess
     * @param boolean $includeWith
     * @param boolean $endProcessTime
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function getView($view, bool $isSuccess, bool $includeWith=true, bool $endProcessTime=true)
    {
        $this->__evalInit();
        $this->setIsSuccess($isSuccess);
        if($endProcessTime)
            $this->setEndProcessTime();
        if($this->isAjax) {
            return $this->getJSON();
        } else {
            $with = $this->getWith($includeWith);
            $val = $this->validateView($view, $with, []);
            if(!$val[0]) throw new exception($val[1]);

            View::share($with);
            AppServiceProvider::initializeMetronic();

            return $val[2];
        }
    }

    public function setSessionFlash()
    {
        $s = config_env('APP_SUCCESS_KEY', '');
        $e = config_env('APP_ERROR_KEY', '');

        if(empty($s)) throw new Exception('env.APP_SUCCESS_KEY is empty');
        if(empty($e)) throw new Exception('env.APP_ERROR_KEY is empty');

        // create success and errors session
        if(session()->has('errors')) {
            session()->flash($e, (session('errors')->getMessageBag()->getMessages() ?? []));
        }
        if(session()->has('success')) {
            $sess = session()->get('success');
            session()->remove('success');
            $sess = array_merge($sess, session()->get($s));
            session()->flash($s, $sess);
        }
    }

    /**
     * Returns `RedirectResponse` or `JsonResponse`
     * 
     * - the `$route_url` will not be used if the request is AJAX, instead you must specify it using the method `setURL()`
     *
     * @param string $route_url
     * @param boolean $is_route_or_url
     * @param boolean $isSuccess
     * @param boolean $includeWith
     * @param boolean $endProcessTime
     * @param array $inputs
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function getRedirect(string $route_url, bool $is_route_or_url, bool $isSuccess, bool $includeWith = true, bool $endProcessTime = true, array $inputs = [])
    {
        
        $this->__evalInit();

        $this->setIsSuccess($isSuccess);
        $sfk = (string)config_env('APP_SESSION_ALERTS_KEY');
        if($endProcessTime)
            $this->setEndProcessTime();
        if($this->isAjax) {
            return $this->getJSON();
        } else {
            $is_valid_url = validate_url($route_url);
            if($is_route_or_url && !Route::has($route_url))
                throw new exception('Route `'.$route_url.'` doesn\'t exists');
            if(!$is_route_or_url && !$is_valid_url)
                throw new exception('Invalid URL: '.$route_url);
                
            $redir_to = $is_route_or_url ? route($route_url) : $route_url;
            $redir = redirect()->to($redir_to);

            // flash alerts
            if(!empty($sfk)) {
                // if(!empty($this->alertsToastr)) session()->flash($sfk.'.toastr', $this->alertsToastr);
                // if(!empty($this->alertsSwal)) session()->flash($sfk.'.swal2', $this->alertsSwal);
                session()->put($sfk.'.toastr', $this->alertsToastr);
                session()->put($sfk.'.swal2', $this->alertsSwal);
            }

            // flash inputs and errors
            if(!empty($this->formErrors)) {
                $redir->withInput($inputs)->withErrors($this->formErrors);
                $this->setSessionFlash();
            }     

            // include with
            if($includeWith) $redir->with($this->getWith(true));

            return $redir;
        }
    }

    /**
     * Gets the logic data
     *
     * @uses \Rguj\Laracore\Library\HttpResponse::hasLogic()
     * @return null|mixed
     */
    public function getLogicData()
    {
        return !$this->hasLogic() ? null : $this->logicData[2];
    }













    // VALIDATORS

    public function validateFieldError(string $key, $val) 
    {
        if(empty($key))
            return [false, 'Invalid key `'.$key.'`'];
        if(!in_array(gettype($val), ['string', 'array']))
            return [false, '`$val` must be string or array of string'];
        if(empty($val))
            return [false, 'Invalid value `'.$key.'`'];
        
        $x = -1;
        foreach($val as $key1=>$val1) {
            $x++;
            if($key1 !== $x)
                return [false, '`$val` must be sequential array'];
            if(!is_string($val1))
                return [false, '`$val`.'.$x.' must be string'];
            if(empty($val1))
                return [false, '`$val`.'.$x.' must not be empty'];
        }

        return [true, ''];
    }

    /**
     * Validates view object or string
     *
     * @param \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|string $view
     * @param \Illuminate\Contracts\Support\Arrayable|array $data
     * @param array $mergeData
     * @return array [ bool, error_msg, \Illuminate\View\View ]
     */    
    public function validateView($view, $data = [], $mergeData = [])
    {
        $obj_list = ['Illuminate\Contracts\View\View', 'Illuminate\Contracts\View\Factory', 'Illuminate\View\View'];
        /** @var \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory $v */
        $v = null;
        $ret = [false, '', null, ''];  // success, err_msg, view_obj, view_name
        try {
            $v_name = '';
            $is_obj = is_object($view);
            if(!(is_string($view) || $is_obj)) throw new exception('Invalid type: $view');
            if(is_string($view)) {
                $v = view($view, $data, $mergeData);
                if(!View::exists($view))
                    throw new exception('View ['.$view.'] not found.');
                $v_name = $view;
            }
            else if(in_array(get_class($view), $obj_list)) {
                $v = $view;
                $v_name = $view->name();
            }
            $ret[0] = true;
            $ret[2] = $v;
            $ret[3] = $v_name;
        } catch(\Exception $ex) {
            $ret[1] = $ex->getMessage();
        }
        return $ret;
    }

    public function validateInputPurpose(string $key, bool $isEncrypted)
    {
        if(!$this->request->has($key))
            return [false, 'Input purpose `'.$key.'` doesn\'t exists'];
        $purpose = $this->request->input($key);
        if($isEncrypted) {
            $crypt = crypt_sc($purpose, 1, true);
            if(!$crypt[0])        
                return [false, 'Could not decrypt input purpose'];
            $purpose = $crypt[2];
        }
        if(!(is_string($purpose) || is_int($purpose)))
            return [false, 'Purpose key must be int or string'];
        return [true, '', $purpose];  // success, msg, purpose
    }

    







    public function isDevMode()
    {
        return $this->isDevMode;
    }










    

    /**
     * Check if request has logic purpose
     *
     * @param boolean $forced fires the logic even if the logic is already triggered
     * @param string $purpose default `_purpose`
     * @param boolean $isEncrypted
     * @return boolean
     */
    public function hasLogic(array $args = [], bool $forced = false, string $purpose = '', bool $isEncrypted = false)
    {
        if($forced || $this->logicTriggered < 1) {
            $this->purposeLogic($args, $purpose, $isEncrypted);
        }
        return is_bool($this->logicData[0]);  // && ($isTrue ? $this->logicData[0] : true);
    }

    
    





}