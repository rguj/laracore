<?php

namespace Rguj\Laracore\Middleware;
use Exception;
use Closure;

use Illuminate\Http\Request as HttpRequest;

use Rguj\Laracore\Request\Request as BaseRequest;
// use Rguj\Laracore\Request\Request;
// use Rguj\Laracore\Library\AppFn;
// use Rguj\Laracore\Library\CLHF;
// use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\WebClient;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// use App\Providers\AppServiceProvider;
use Rguj\Laracore\Provider\BaseAppServiceProvider as AppServiceProvider;
use App\Core\Adapters\Theme;
use App\Models\ModelHasRole;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Url\Url as SpatieUrl;
use Illuminate\Support\ViewErrorBag;

/**
 * A wrapper for web middleware
 *
 * @subpackage \Rguj\Laracore\Helper\Helper
 */
class ClientInstanceMiddleware
{
    // public string $default_timezone;  // = 'Asia/Taipei';
    // public string $home;

    /** @var object $role */
    protected $role;
    protected array $role2;

    // public const ROLES = [
    //     [1, 'admin', 'Administrator'],
    //     [2, 'rstaff', 'Registrar Staff'],
    //     [3, 'eofficer', 'Enrollment Officer'],
    //     [4, 'student', 'Student'],
    //     [5, 'cashier', 'Cashier'],
    //     [6, 'cstaff', 'Clinic Staff'],
    //     [7, 'jappl', 'Job Applicant'],
    //     [8, 'misstaff', 'MIS Staff'],
    //     [9, 'osdsstaff', 'OSDS Staff'],
    // ];

    // public const ROLE_ADMIN       = 1;
    // public const ROLE_RSTAFF      = 2;
    // public const ROLE_EOFFICER    = 3;
    // public const ROLE_STUDENT     = 4;
    // public const ROLE_CASHIER     = 5;
    // public const ROLE_CSTAFF      = 6;
    // public const ROLE_JAPPL       = 7;
    // public const ROLE_MISSTAFF    = 8;
    // public const ROLE_OSDSSTAFF   = 9;

    public const GLOBAL_THROTTLE = [5, 1];  // [ attempts, decay_mins ]

    // private int $users_count;
    private bool $is_auth;
    private bool $is_admin;
    private bool $force_register;

    private $request;
    private $client_info;
    private $user_info;

    private array $bypassAuthRoutes;
    private array $guestRoutes;

    private $url_login;
    private $url_register;
    private $url_verify_email;
    private $url_api;
    private $url_home;
    private $url_intended;


    public function __construct()
    {
        // $this->__renderRoles();

        // $this->url_login = route('login');
        // $this->url_register = route('register');
        $this->url_login = route(env('ROUTE_LOGIN'));
        $this->url_register = route(env('ROUTE_REGISTER'));
        $this->url_verify_email = route(env('ROUTE_VERIFY_EMAIL'));
        $this->url_api = route(env('ROUTE_API'));
        $this->url_home = route(env('ROUTE_HOME'));
        // $this->url_home = route('home.index');
        $this->url_intended = webclient_intended();

        //$this->home = $this->url_home;
        $this->force_register = (bool)config('z.base.register.force_if_empty_user');
        $this->is_auth = cuser_is_auth();
        $this->is_admin = false;


        $this->bypassAuthRoutes = [
            $this->url_api,

        ];

        $this->guestRoutes = [
            $this->url_login,
            $this->url_register,
            $this->url_api,

        ];

    }







    /**
     * Handle an incoming request.
     *
     * @param  HttpRequest  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(HttpRequest $req, Closure $next)
    {

        // dd(asset('etet'));

		// dump(2);
        $request = resolve(BaseRequest::class);
        $this->request = $request;

        // set user info
        $id = cuser_id();
        $roles_arr = z_roles(false);
        point1:
        $this->user_info = SELF::user_info_($id);
        // $this->is_admin = arr_colval_exists(z_roles()->admin, $this->user_info['types'] ?? [], 'role_id', true);
        $this->is_admin = in_array(z_roles()->admin, $this->user_info['userroles'] ?? [], true);
        Arr::set($this->user_info, 'settings.timezone', Arr::get($this->user_info, 'settings.timezone', config('app.timezone')));
        // config()->set('z.user', $this->user_info);

        config()->set('z.user.settings.timezone', $this->user_info['settings']['timezone']);

        // fix admin roles
        if($this->is_admin) {
            if(count($roles_arr) !== count($this->user_info['userroles'])) {
                foreach($roles_arr as $k=>$v) {
                    $a = arr_search_by_key($this->user_info['userroles'], 'role_id', $v);
                    if(empty($a)) {
                        RoleUser::updateOrCreate(['role_id' => $v, 'user_id' => $this->user_info['id']], []);
                        ModelHasRole::updateOrCreate(['model_id' => $this->user_info['id'], 'role_id' => $v, 'model_type' => User::class], []);
                    }
                }
                goto point1;
            }
        }

        // set client info
        $this->client_info = SELF::client_info_($request);

        // dd($this->client_info);

        if(!$this->client_info[0]) {
            jed($this->client_info);
            throw new Exception('Unable to issue client info');
        }
        $this->client_info = $this->client_info[2];
        Config::set('client', $this->client_info);

        // some logic for debugging
        $has_dev_key = webclient_is_dev();
        // if($this->is_admin || $has_dev_key) {
        if($this->is_admin) {
            app('debugbar')->enable();
            Config::set('app.debug', true);
        } else {
            // app('debugbar')->disable();
            // Config::set('app.debug', false);
        }

        // MAINTENANCE MODE
        if(env('MAINTENANCE_MODE', false) && !app()->runningInConsole() && !webclient_is_dev()) {
            abort(503, 'Under maintenance');
        }

        // some validation
        $validate = $this->validate($request);  // added new
        // $validate = [true, 0, null];

        if(webclient_is_dev()) {

        }

        // dd(cuser_data());

        if($validate[2] === 'https://hris2.localhost.com' || $validate[2] === 'https://hris2.localhost.com/') {
            // dd(656587574);
            $validate[2] = url()->previous();
        }

        // dump($validate);

        if(!$validate[0]) {


            // if(!is_string($validate[2])) {
            //     throw new Exception('Parameter 3 must be string');
            // }

            if($validate[1] === 2 && (!is_string($validate[2]) || empty($validate[2]))) {
                $validate[2] = url()->previous();
            }

            switch($validate[1]) {
                // $validate [success, err_mode, err_data]
                // err_mode [1 => exception, 2 => redirect]
                case 1:
                    // dd(1);
                    throw new Exception($validate[2]);
                case 2:
                    // dump($validate);
                    // dd(2);
                    return redirect()->to($validate[2]);
                default:
                    // dd(3);
                    throw new Exception('Invalid mode');
            }
        }

        // set config
        // Config::set('user', $this->user_info);

        // point1:


        // set user theme mode
        $theme_mode = config('z.user.settings.theme_mode') ?? '';
        $theme_mode = in_array($theme_mode, ['light', 'dark']) ? $theme_mode : 'light';
        Config::set('z.demoa.general.layout.aside.theme', $theme_mode);
        // override global menu
        Config::set('z.global.menu', $this->user_menu($request, false));

        // trigger theme bootstrap
        AppServiceProvider::initializeMetronic();

        // decrypt purpose
        crypt_de_merge_get($request, 'p', true, false);
        crypt_de_merge_get($request, '_purpose', true, false);

        return $next($req);
    }




    public static function logout(BaseRequest $request, string $redirect = '/')
    {
        // $fb = session_get_alerts(true);

        Auth::guard('web')->logout();
        session()->forget('app.feedbacks');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $redirect_to = !empty($redirect) ? redirect()->to($redirect) : '/';
        session_push_alert('success', 'Logged out successfully');

        // unset($fb['swal2']); unset($fb['toastr']);
        // foreach($fb as $k=>$v) {
        //     session_push_alert($v['status'], $v['msg'], $v['title'], $v['type']);
        // }
        // session_push_alert('success', 'Logged out successfully');

        return redirect()->to($redirect_to);
    }

    // some user and client validation
    private function validate(BaseRequest $request) {
        // returns [success, err_mode, err_data]
        // err_mode [1 => exception, 2 => redirect]

        // check useragent browser
        $this->evalBrowser();

        // $fb = session_get_alerts(true);
        $url = url_parse(request()->fullUrl());
        $schemeHostPath = $url->schemeHostPath;

        if(in_array($schemeHostPath, $this->bypassAuthRoutes)) {
            // dd(654654);
            goto point1;
        }
        // dd(222);

        if(webclient_is_dev()) {
            // dd($this->user_info);
        }

        if(in_array($url->path, ['', '/'])) {
            // dump(0);
            goto point1;
        }

        if($this->is_auth) {

            // auto fix missing user child tables
            // fix user_state
            // in login script [DONE]



            // check account state
            // if(!$this->user_info['is_active']) {
            //     session_push_alert('error', 'Account is deactivated');
            //     $logout = $this->logout($request, route('index.index'));


            //     unset($fb['swal2']);
            //     unset($fb['toastr']);
            //     foreach($fb as $k=>$v) {
            //         session_push_alert($v['status'], $v['msg'], $v['title'], $v['type']);
            //     }


            //     return [false, 2, $logout];
            // }




            // dd($this->user_info);
            // check email verify
            if(!$this->user_info['verify']['email']['is_verified']) {
                if($schemeHostPath !== $this->url_verify_email) {
                    // dd(11);
                    session_push_alert('info', 'Please verify your email first');
                    return [false, 2, $this->url_verify_email];
                }
            } else {
                if($schemeHostPath === $this->url_verify_email) {
                    // dd(22);
                    session_push_alert('success', 'Account is already verified.');
                    // return [false, 2, $this->url_home];
                    return [false, 2, $this->url_intended];
                }
            }
        } else {
            // check if no user

            $is_url_registration = ($schemeHostPath === $this->url_register);

            if($this->force_register && config('z.user-count') < 1 && !$is_url_registration) {
                // dd(33);
                session_push_alert('info', 'Please register first');
                return [false, 2, $this->url_register];
            }
            elseif($is_url_registration) {
                // dump($this->url_register);
                // dump($schemeHostPath);
                // dd(44);
                goto point1;
            }
            else {
                if($schemeHostPath === $this->url_login || $schemeHostPath === route(env('ROUTE_PASSWORD_REQUEST'))) {
                    // dd(55);
                    goto point1;
                }
                // dump($schemeHostPath);
                // dump($this->url_login);
                // dd(66);
                session_push_alert('error', 'Please login first.');
                return [false, 2, $this->url_login];
            }
        }
        point1:
        return [true, null, null];
    }









    public static function getAvatarUrl(string $file, string $theme_mode)
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('user_avatar');
        $url = $disk->path($file);
        // $file_url = CLHF::STORAGE_FileURL($url, 'dispose');
        $file_url = storage_file_url($url, 'dispose');

        // if file avatar exist in storage folder
        if(!empty($file_url)) {
            return $file_url;  // get avatar url from storage
        }

        // check if the avatar is an external url, eg. image from google
        // if (filter_var($file, FILTER_VALIDATE_URL)) {
        //     return $file;
        // }

        // no avatar, return blank avatar
        $img_blank = asset('demoa/media/avatars/blank'.($theme_mode === 'dark' ? '-dark' : '').'.png');

        return $img_blank;
    }





    public static function user_info_($id) {

        $tblUser = db_model_table_name(config('auth.providers.users.model'));
        $tblRole = db_model_table_name(config('permission.models.role'));
        // $tblUserType = db_model_table_name(\App\Models\UserType::class);
        $tblUserType = db_model_table_name(RoleUser::class);

        // get user data
        $user = [];
        if(!is_null($id)) {
            $u = db_model_table_name(config('auth.providers.users.model'));  // user table;
            $user_ = config('auth.providers.users.model')::with([
                'settings' => function(HasMany $q) use($id) {
                    /** @var \Illuminate\Database\Query\Builder $q */
                    list($t, $p) = db_relation_info($q);

                    $q->select(['user_id', $t.'.setting_key', $t.'.setting_value']);
                },

                // 'theme' => function($q) {
                //     $q->select(['user_id', 'ac_user_theme.theme', 'ac_user_theme.mode']);
                // },

                // 'state' => function(HasOne $q) use($id) {
                //     /** @var \Illuminate\Database\Query\Builder $q */
                //     list($t, $p) = db_relation_info($q);

                //     $q->select(['user_id', 'is_active']);
                // },
                // 'verifyemail' => function(HasOne $q) use($id) {
                //     /** @var \Illuminate\Database\Query\Builder $q */
                //     list($t, $p) = db_relation_info($q);
                //     // $q->select(['user_id', 'verified_at']);
                //     $q->where($t.'.verified_at', '<>', '')
                //         ->where($t.'.verified_at', '<>', null)
                //         ->select(['user_id', 'verified_at']);
                // },
                'userroles' => function(HasMany $q) use($id) {
                    /** @var \Illuminate\Database\Query\Builder $q */
                    list($t, $p) = db_relation_info($q);
                    $r = db_model_table_name(config('permission.models.role'));  // role table
                    $q->join($r,  $r.'.id', '=',  $t.'.role_id');
                    // $q->select(['user_id',  $r.'.id AS role_id',  $r.'.title',  $r.'.short']);
                    $q->select(['user_id',  $r.'.id AS role_id', $r.'.name']);
                    // $q->where([ $t.'.is_valid'=>1]);
                    $q->orderBy( $t.'.role_id', 'asc');
                },
                // 'info' => function(HasOne $q) use($id) {dd($q);
                //     /** @var \Illuminate\Database\Query\Builder $q */
                //     list($t, $p) = db_relation_info($q);

                //     $q->select(['user_id', $t.'.*']);
                // },
            ])
            ->where($u.'.id', '=', $id)
            ->get()//->toArr(0)
            // ->toSql()
            ;
            $user = $user_->toArr(0);
            $user_ = $user_[0];


            // harmonize user settings array
            $user_settings = [];
            array_walk($user['settings'], function($v, $k) use(&$user_settings) {
                $user_settings[$v['key']] = $v['value'];
            });
            $user['settings'] = $user_settings;

            // $user['is_active'] = arr_get($user, 'state.is_active', 0) === 1;
            $user['is_active'] = arr_get($user, 'activated_at') !== null;

            // $emailverify = dt_parse(arr_get($user, 'verifyemail.verified_at', ''));
            // dd($user_->email_verified_at->format(dt_standard_format()));
            $emailverify = dt_parse(($user_->email_verified_at?->format(dt_standard_format()) ?? '') ?? '');
            // dd($emailverify);

            // $user['verifyemail'] = !is_array($user['verifyemail']) ? [] : $user['verifyemail'];
            // $user['verify'] = [
            //     'email' => [
            //         'is_verified' => (bool)($emailverify->is_valid ?? false),
            //         'verified_at' => (string)($emailverify->string->onto ?? ''),
            //     ],
            // ];

            $roles = [];
            foreach((array)$user['userroles'] as $k=>$v) {
                $roles[$v['name']] = $v['role_id'];
            }
            $user['userroles'] = $roles;

            $user['verify']['email'] = [
                'is_verified' => (bool)($emailverify->is_valid ?? false),
                'verified_at' => (string)($emailverify->string->onto ?? ''),
            ];


            // $user['info']['avatar_url'] = SELF::getAvatarUrl($user['info']['avatar'] ?? '', $user['settings']['theme_mode'] ?? 'light');
            $user['info']['avatar_url'] = '';
            $user['name'] = str_sanitize($user['name'] ?? $user['fname'].' '.$user['lname']);
            // dd($user);

        }

        if($id === 14880) {

        }
        return $user;
    }



    // public static function client_info_(Request $request) {
    //     $has_tz = $request->has('_timezone');
    //     $tz_before = $request->input('_timezone');

    //     $request->merge(['_timezone'=>config('app.timezone')]);  // override timezone
    //     $client_info = WebClient::getClientUAInfo($request);  // get client info

    //     // delete or put back
    //     if($has_tz) {
    //         $request->merge(['_timezone'=>$tz_before]);
    //     } else {
    //         $request->request->remove('_timezone');
    //         $request->query->remove('_timezone');
    //     }

    //     return $client_info;
    // }
    public static function client_info_(BaseRequest $request) {
        // $request->merge(['_timezone'=>config('app.timezone')]);  // override timezone
        $client_info = WebClient::getClientUAInfo($request);  // get client info
        return $client_info;
    }




    public function redirect(bool $goto_intended_url, bool $return_object=true) {
        // does not check is_valid
        // @param $return_object ? redirect_object : route_string

        // $user_role_ids = array_column(config('z.user.types'), 'id');
        $user_role_ids = z_roles_ids();

        # config
        $redirect_to_str = null;
        $default = $this->url_home;
        $user_role_defaults = [  # role_id => URI
            // 1    => '/administrator',
            // 2    => '/student',
        ];

        // GOTO INTENDED URL, SKIP IF EMPTY URL
        if($goto_intended_url) {
            // AppFn::STR_Sanitize($next_url, false, true);  // trim next_url
            $next_url = $goto_intended_url ? redirect()->intended()->getTargetUrl() : '';
            $next_url = $next_url === config('app.url') ? $default : $next_url;
            if(!empty($next_url)) {
                $redirect_to_str = $next_url;
                goto point1;
            }
        }

        // convert all to lower case
        foreach($user_role_defaults as $key=>$val) {
            $user_role_defaults[$key] = strtolower($val);
        }

        $x_user_role_ids = count($user_role_ids);
        if($x_user_role_ids < 1 || $x_user_role_ids > 1) {  // if no / multiple roles, just default
            $redirect_to_str = $default;
        } else {                                            // if single role, specify
            $user_role_id = $user_role_ids[0][0] ?? '';
            if(array_key_exists($user_role_id, $user_role_defaults)) {  // if role_id matches
                $redirect_to_str = $user_role_defaults[$user_role_id];
            } else {                                                    // if no match, just default
                $redirect_to_str = $default;
            }
        }
        point1:
        $redirect_to = $return_object ? redirect()->to($redirect_to_str) : $redirect_to_str;
        return $redirect_to;
    }




    public function evalBrowser(bool $skipRootPath = true)
    {
        $skip = [
            '/',
            'check/iops',
        ];

        $ua = WebClient::__getUA();
        // config()->set('z.browser', []);

        // if($skipRootPath && request()->path() === '/') {
        if($skipRootPath && in_array(request()->path(), $skip)) {
            goto point2;
        }

        //dd($ua);
        // CHECK BROWSER
        $vb = function() use($ua) {
            $flagged = false;
            // $invalid_type = false;
            // $invalid_name = false;
            // $invalid_browser = false;
            // $invalid_referrer = false;
            $err_msg = '';
            try {
                // $ua = WebClient::__getUA();
                if(empty($ua)) {
                    // $invalid_browser = true;
                    throw new exception('Failed to get user-agent info');
                }
                $ba = config('z.browser.requirement');

                if(!array_key_exists($ua['device']['type'], $ba)) {
                    config()->set('z.browser.is_valid_type', false);
                    throw new exception('Invalid device type');
                }
                config()->set('z.browser.type', $ua['device']['type']);

                if(!array_key_exists($ua['browser']['name'], $ba[$ua['device']['type']])) {
                    config()->set('z.browser.is_valid_name', false);
                    throw new exception('Invalid device name');
                }
                config()->set('z.browser.name', $ua['browser']['name']);

                $version_required = $ba[$ua['device']['type']][$ua['browser']['name']];
                $version_current = $ua['browser']['version'];
                config()->set('z.browser.version.required', $version_required);
                config()->set('z.browser.version.current', $version_current);

                // $flagged = false;
                if(str_version_compare($version_current, $version_required, '<')) {
                    config()->set('z.browser.is_outdated', true);
                    $flagged = true;
                }

                $http_referrer = $_SERVER['HTTP_REFERER'] ?? '';
                $allowed_host = (array)config('z.browser.allowed_host');
                $blocked_host = (array)config('z.browser.blocked_host');
                $up = url_parse($http_referrer);

                if(
                    // in_array($http_referrer, $block_referrer, true)
                    ($up->is_valid && in_array($up->host, $blocked_host, true))
                    || ($up->is_valid && !in_array($up->host, $allowed_host, true))
                    || (!str_empty($http_referrer) && !$up->is_valid)
                ) {
                    config()->set('z.browser.is_blocked_referrer', true);
                    $flagged = true;
                }

                if($flagged) {
                    if(!config('z.browser.is_valid_type')) {
                        throw new exception('Unsupported referrer');
                    }
                    if(!config('z.browser.is_valid_name')) {
                        throw new exception('Unsupported referrer');
                    }
                    if(!config('z.browser.is_outdated')) {
                        throw new exception('Unsupported browser');
                    }
                    if(!config('z.browser.is_blocked_referrer')) {
                        throw new exception('Unsupported referrer');
                    }
                }

            } catch(Exception $ex) {
                $err_msg = $ex->getMessage();

                if(!in_array($ua['device']['name'] ?? '', (array)config('z.browser.bypass_device_name'))) {
                    config()->set('z.browser.is_valid', false);
                    config()->set('z.browser.err_msg', $err_msg);
                    //abort(response()->view('errors.unsupported-browser', config('z.browser'), 406));
                }

            }
            point1:

        };
        $vb();
        point2:
        config()->set('z.browser.useragent', $ua);

        // updatemybrowser.org minumum requirements
        $umb = [];
        $buo = [];  // https://browser-update.org
        $buo_keys = [
            'edge' => 'e',
            'ie' => 'i',
            'chrome' => 'c',
            'firefox' => 'f',
            'opera' => 'o',
            // 'opera android' => 'o_a',
            'safari' => 's',
            'yandex' => 'y',
            'vivaldi' => 'v',
            'ucbrowser' => 'uc',
            'iosbrowser' => 'ios',
            'samsung' => 'samsung',
        ];
        $device_type = (string)config('z.browser.useragent.device.type');
        $req = !empty($device_type) ? (array)config('z.browser.requirement.'.$device_type) : [];
        foreach($req as $k=>$v) {
            $k2 = strtolower($k);
            $v2 = (float)$v;
            $umb[$k2] = $v2;
            if(array_key_exists($k2, $buo_keys)) {
                $buo[$buo_keys[$k2]] = $v;
            }
        }
        config()->set('z.browser.umb', $umb);
        config()->set('z.browser.buo', $buo);


    }









    public function user_menu(BaseRequest $request, bool $strict = true)
    {
        $category = function(string $cat) {
            return [
                'classes' => ['content' => 'pt-8 pb-2'],
                'content' => '<span class="menu-section text-muted text-uppercase fs-8 ls-1">'.$cat.'</span>',
            ];
        };

        $show_roles = [
            // use the short value from the database and it must coincide with the name of the config file
            // set the ordering here

            // 'jappl',
            'student',
            'eofficer',
            'rstaff',
            'osdsstaff',

            'admin',
        ];
        array_unique($show_roles);

        // get the menu of each role
        $main = [];
        array_push($main, ...(array)config('z.menu.all'));
        // $rs = array_column((array)config('z.user.types'), 'name');
        $rs = z_roles_names();
        if(cuser_is_auth()) {
            foreach($show_roles as $k1=>$v1) {
                if(!in_array($v1, $rs))
                    continue;

                $lbl = [false, ''];
                foreach(z_roles_arr() as $k2=>$v2) {
                    if($v2[1] === $v1) {
                        $lbl = [true, $v2[2]];
                        break;
                    }
                }
                if($strict) {
                    // if(!$lbl[0]) {
                    //     throw new exception('Role `'.$v2['short'].'` not found. [DB]');
                    // }
                    if(!file_exists(config_path('/core/menu/'.$v1.'.php')))
                        throw new exception('Role `'.$v1.'` not found. [File]');
                }

                $m = config('z.menu.'.$v1);
                if(!empty($m)) {
                    // array_unshift($m, $category($lbl[1]));
                    // array_push($main, $m);
                    array_unshift($m, $category($lbl[1]));
                    foreach($m as $k2=>$v2) {
                        array_push($main, $v2);
                    }
                }
            }
            if(!empty(config('z.menu.user'))) {
                array_push($main, $category('User'), config('z.menu.user'));
            }
        } else {
            if(!empty(config('z.menu.guest'))) {
                array_push($main, $category('Guest'), ...(array)config('z.menu.guest'));
            }
        }

        // remove empty elements
        $main2 = [];
        foreach($main as $k1=>$v2) {
            if(!empty($v2)) $main2[] = $v2;
        }

        return [
            'documentation' => $this->user_menu_documentation($request),
            // 'horizontal' => $this->user_menu_horizontal($request),
            'horizontal' => $this->user_menu_horizontal(),
            // 'main' => $this->user_menu_main($request),
            'main' => $main2,
        ];
    }



    private function user_menu_main(BaseRequest $request)
    {
        $data = [
            //// Dashboard
            [
                'title' => 'Dashboard',
                'path'  => '',
                'icon'  => theme()->getSvgIcon("demo1/media/icons/duotune/art/art002.svg", "svg-icon-2"),
            ],
        ];


        array_push($data,
            //// Modules
            [
                'classes' => ['content' => 'pt-8 pb-2'],
                'content' => '<span class="menu-section text-muted text-uppercase fs-8 ls-1">Modules</span>',
            ],
            // Account
            [
                'title'      => 'Account',
                'icon'       => [
                    'svg'  => theme()->getSvgIcon("demo1/media/icons/duotune/communication/com006.svg", "svg-icon-2"),
                    'font' => '<i class="bi bi-person fs-2"></i>',
                ],
                'classes'    => ['item' => 'menu-accordion'],
                'attributes' => [
                    "data-kt-menu-trigger" => "click",
                ],
                'sub'        => [
                    'class' => 'menu-sub-accordion menu-active-bg',
                    'items' => [
                        [
                            'title'  => 'Overview',
                            'path'   => 'account/overview',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                        [
                            'title'  => 'Settings',
                            'path'   => 'account/settings',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                        [
                            'title'      => 'Security',
                            'path'       => '#',
                            'bullet'     => '<span class="bullet bullet-dot"></span>',
                            'attributes' => [
                                'link' => [
                                    "title"             => "Coming soon",
                                    "data-bs-toggle"    => "tooltip",
                                    "data-bs-trigger"   => "hover",
                                    "data-bs-dismiss"   => "click",
                                    "data-bs-placement" => "right",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // System
            [
                'title'      => 'System',
                'icon'       => [
                    'svg'  => theme()->getSvgIcon("demo1/media/icons/duotune/general/gen025.svg", "svg-icon-2"),
                    'font' => '<i class="bi bi-layers fs-3"></i>',
                ],
                'classes'    => ['item' => 'menu-accordion'],
                'attributes' => [
                    "data-kt-menu-trigger" => "click",
                ],
                'sub'        => [
                    'class' => 'menu-sub-accordion menu-active-bg',
                    'items' => [
                        [
                            'title'      => 'Settings',
                            'path'       => '#',
                            'bullet'     => '<span class="bullet bullet-dot"></span>',
                            'attributes' => [
                                'link' => [
                                    "title"             => "Coming soon",
                                    "data-bs-toggle"    => "tooltip",
                                    "data-bs-trigger"   => "hover",
                                    "data-bs-dismiss"   => "click",
                                    "data-bs-placement" => "right",
                                ],
                            ],
                        ],
                        [
                            'title'  => 'Audit Log',
                            'path'   => 'log/audit',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                        [
                            'title'  => 'System Log',
                            'path'   => 'log/system',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                    ],
                ],
            ],
            // Separator
            // [
            //     'content' => '<div class="separator mx-1 my-4"></div>',
            // ],
            // Changelog
            // [
            //     'title' => 'Changelog v'.theme()->getVersion(],
            //     'icon'  => theme()->getSvgIcon("demo1/media/icons/duotune/general/gen005.svg", "svg-icon-2"),
            //     'path'  => 'documentation/getting-started/changelog',
            // ],
        );

        return $data;
    }



    private function user_menu_documentation(BaseRequest $request)
    {
        return !cuser_is_admin() ? [] : [
            // Getting Started
            [
                'heading' => 'Getting Started',
            ],
            // Overview
            [
                'title' => 'Overview',
                'path'  => 'documentation/getting-started/overview',
            ],
            // Build
            [
                'title' => 'Build',
                'path'  => 'documentation/getting-started/build',
            ],
            [
                'title'      => 'Multi-demo',
                'attributes' => ["data-kt-menu-trigger" => "click"],
                'classes'    => ['item' => 'menu-accordion'],
                'sub'        => [
                    'class' => 'menu-sub-accordion',
                    'items' => [
                        [
                            'title'  => 'Overview',
                            'path'   => 'documentation/getting-started/multi-demo/overview',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                        [
                            'title'  => 'Build',
                            'path'   => 'documentation/getting-started/multi-demo/build',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                    ],
                ],
            ],
            // File Structure
            [
                'title' => 'File Structure',
                'path'  => 'documentation/getting-started/file-structure',
            ],
            // Customization
            [
                'title'      => 'Customization',
                'attributes' => ["data-kt-menu-trigger" => "click"],
                'classes'    => ['item' => 'menu-accordion'],
                'sub'        => [
                    'class' => 'menu-sub-accordion',
                    'items' => [
                        [
                            'title'  => 'SASS',
                            'path'   => 'documentation/getting-started/customization/sass',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                        [
                            'title'  => 'Javascript',
                            'path'   => 'documentation/getting-started/customization/javascript',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                    ],
                ],
            ],
            // Dark skin
            [
                'title' => 'Dark Mode Version',
                'path'  => 'documentation/getting-started/dark-mode',
            ],
            // RTL
            [
                'title' => 'RTL Version',
                'path'  => 'documentation/getting-started/rtl',
            ],
            // Troubleshoot
            [
                'title' => 'Troubleshoot',
                'path'  => 'documentation/getting-started/troubleshoot',
            ],
            // Changelog
            [
                'title'            => 'Changelog <span class="badge badge-changelog badge-light-danger bg-hover-danger text-hover-white fw-bold fs-9 px-2 ms-2">v'.theme()->getVersion().'</span>',
                'breadcrumb-title' => 'Changelog',
                'path'             => 'documentation/getting-started/changelog',
            ],
            // References
            [
                'title' => 'References',
                'path'  => 'documentation/getting-started/references',
            ],
            // Separator
            [
                'custom' => '<div class="h-30px"></div>',
            ],
            // Configuration
            [
                'heading' => 'Configuration',
            ],
            // General
            [
                'title' => 'General',
                'path'  => 'documentation/configuration/general',
            ],
            // Menu
            [
                'title' => 'Menu',
                'path'  => 'documentation/configuration/menu',
            ],
            // Page
            [
                'title' => 'Page',
                'path'  => 'documentation/configuration/page',
            ],
            // Page
            [
                'title' => 'Add NPM Plugin',
                'path'  => 'documentation/configuration/npm-plugins',
            ],
            // Separator
            [
                'custom' => '<div class="h-30px"></div>',
            ],
            // General
            [
                'heading' => 'General',
            ],
            // DataTables
            [
                'title'      => 'DataTables',
                'classes'    => ['item' => 'menu-accordion'],
                'attributes' => ["data-kt-menu-trigger" => "click"],
                'sub'        => [
                    'class' => 'menu-sub-accordion',
                    'items' => [
                        [
                            'title'  => 'Overview',
                            'path'   => 'documentation/general/datatables/overview',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                    ],
                ],
            ],
            // Remove demos
            [
                'title' => 'Remove Demos',
                'path'  => 'documentation/general/remove-demos',
            ],
            // Separator
            [
                'custom' => '<div class="h-30px"></div>',
            ],
            // HTML Theme
            [
                'heading' => 'HTML Theme',
            ],
            [
                'title' => 'Components',
                'path'  => '//preview.keenthemes.com/metronic8/demo1/documentation/base/utilities.html',
            ],
            [
                'title' => 'Documentation',
                'path'  => '//preview.keenthemes.com/metronic8/demo1/documentation/getting-started.html',
            ],
        ];
    }


    public function user_menu_horizontal()
    {
        return !cuser_is_admin() ? [] : [
            // Dashboard
            // [
            //     'title'   => 'Dashboard',
            //     'path'    => '',
            //     'classes' => ['item' => 'me-lg-1'],
            // ],
            // Resources
            [
                'title'      => 'Resources',
                'classes'    => ['item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-none'],
                'attributes' => [
                    'data-kt-menu-trigger'   => "click",
                    'data-kt-menu-placement' => "bottom-start",
                ],
                'sub'        => [
                    'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
                    'items' => [
                        // Documentation
                        [
                            'title' => 'Documentation',
                            'icon'  => theme()->getSvgIcon("demo1/media/icons/duotune/abstract/abs027.svg", "svg-icon-2"),
                            'path'  => 'documentation/getting-started/overview',
                        ],

                        // Changelog
                        [
                            'title' => 'Changelog v'.theme()->getVersion(),
                            'icon'  => theme()->getSvgIcon("demo1/media/icons/duotune/general/gen005.svg", "svg-icon-2"),
                            'path'  => 'documentation/getting-started/changelog',
                        ],
                    ],
                ],
            ],
            // Account
            [
                'title'      => 'Account',
                'classes'    => ['item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-none'],
                'attributes' => [
                    'data-kt-menu-trigger'   => "click",
                    'data-kt-menu-placement' => "bottom-start",
                ],
                'sub'        => [
                    'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
                    'items' => [
                        [
                            'title'  => 'Overview',
                            'path'   => 'account/overview',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                        [
                            'title'  => 'Settings',
                            'path'   => 'account/settings',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                        [
                            'title'      => 'Security',
                            'path'       => '#',
                            'bullet'     => '<span class="bullet bullet-dot"></span>',
                            'attributes' => [
                                'link' => [
                                    "title"             => "Coming soon",
                                    "data-bs-toggle"    => "tooltip",
                                    "data-bs-trigger"   => "hover",
                                    "data-bs-dismiss"   => "click",
                                    "data-bs-placement" => "right",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // System
            [
                'title'      => 'System',
                'classes'    => ['item' => 'menu-lg-down-accordion me-lg-1', 'arrow' => 'd-lg-none'],
                'attributes' => [
                    'data-kt-menu-trigger'   => "click",
                    'data-kt-menu-placement' => "bottom-start",
                ],
                'sub'        => [
                    'class' => 'menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px',
                    'items' => [
                        [
                            'title'      => 'Settings',
                            'path'       => '#',
                            'bullet'     => '<span class="bullet bullet-dot"></span>',
                            'attributes' => [
                                'link' => [
                                    "title"             => "Coming soon",
                                    "data-bs-toggle"    => "tooltip",
                                    "data-bs-trigger"   => "hover",
                                    "data-bs-dismiss"   => "click",
                                    "data-bs-placement" => "right",
                                ],
                            ],
                        ],
                        [
                            'title'  => 'Audit Log',
                            'path'   => 'log/audit',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                        [
                            'title'  => 'System Log',
                            'path'   => 'log/system',
                            'bullet' => '<span class="bullet bullet-dot"></span>',
                        ],
                    ],
                ],
            ],
        ];
    }









}
