<?php

namespace Rguj\Laracore\Middleware;

use Closure;
use Illuminate\Http\Request;

use \App\Libraries\AppFn;
use \App\Libraries\CLHF;
use \App\Libraries\DT;
use \App\Libraries\WebClient;
use \App\Libraries\Prologue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
// use Barryvdh\Debugbar\Facade as Debugbar;
use \App\Models\User;
use Exception;



use App\Providers\AppServiceProvider;
use App\Core\Adapters\Theme;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class ClientInstanceMiddleware
{
    // public string $default_timezone;  // = 'Asia/Taipei';
    // public string $home;

    public const ROLES = [
        [1, 'admin', 'Administrator'],
        [2, 'rstaff', 'Registrar Staff'],
        [3, 'eofficer', 'Enrollment Officer'],
        [4, 'student', 'Student'],
        [5, 'cashier', 'Cashier'],
        [6, 'cstaff', 'Clinic Staff'],
        [7, 'jappl', 'Job Applicant'],
    ];

    public const ROLE_ADMIN    = 1;
    public const ROLE_RSTAFF   = 2;
    public const ROLE_EOFFICER = 3;
    public const ROLE_STUDENT  = 4;
    public const ROLE_CASHIER  = 5;
    public const ROLE_CSTAFF   = 6;
    public const ROLE_JAPPL    = 7;

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
    

    public function __construct()
    {
        $this->url_login = route('login');
        $this->url_register = route('register');
        $this->url_verify_email = route('verification.notice');
        $this->url_api = route('api.index');
        $this->url_home = route('home.index');

        //$this->home = $this->url_home;
        // $this->default_timezone = config('env.APP_TIMEZONE');
        $this->force_register = config('env.APP_NO_USER_FORCE_REGISTER');
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // dd(12344421);

        // CHECK URI
        // $route_name = null;
        // try { $route_name = $request->route()->getName(); } catch(\Throwable $ex) {}
        // abort_if(empty($route_name), 404);

        $this->request = $request;
        
        // set user info
        $id = cuser_id();
        $this->user_info = SELF::user_info_($id);
        $this->is_admin = arr_colval_exists(SELF::ROLE_ADMIN, $this->user_info['types'] ?? [], 'role_id', true);
        Arr::set($this->user_info, 'settings.timezone', Arr::get($this->user_info, 'settings.timezone', env('APP_TIMEZONE', 'UTC')));
        Config::set('user', $this->user_info);
        
        // set client info
        $this->client_info = SELF::client_info_($request);
        if(!$this->client_info[0])
            throw new Exception('Unable to issue client info');
        $this->client_info = $this->client_info[2];
        Config::set('client', $this->client_info);
        

        // some logic for debugging
        if($this->is_admin) {
            app('debugbar')->enable();
            Config::set('app.debug', true);
        } else {
            if($request->server('REMOTE_ADDR') !== '127.0.0.1') {
                app('debugbar')->disable();
                Config::set('app.debug', false);
            }
        }
        
        // some validation
        $validate = $this->validate();
        // dd($validate);
        if(!$validate[0]) {
            if(!is_string($validate[2]))
                throw new Exception('Parameter 3 must be string');
            
            switch($validate[1]) {
                case 1:
                    throw new Exception($validate[2]);
                case 2:
                    return redirect()->to($validate[2]);
                default:
                    throw new Exception('Invalid mode');
            }
        }
       
        // set config
        // Config::set('user', $this->user_info);

        // set user theme mode
        $theme_mode = config('user.settings.theme_mode') ?? '';
        $theme_mode = in_array($theme_mode, ['light', 'dark']) ? $theme_mode : 'light';
        Config::set('demoa.general.layout.aside.theme', $theme_mode);
        // dd(12321);
        // override global menu
        Config::set('global.menu', $this->user_menu($request));

        // trigger theme bootstrap
        AppServiceProvider::initializeMetronic();
        
        // decrypt purpose
        crypt_de_merge_get($request, 'p', true, false);
        crypt_de_merge_get($request, '_purpose', true, false);
        
        // dd(3213);
        return $next($request);
    }








    public static function logout(Request $request, string $redirect = '/')
    {
        Auth::guard('web')->logout();
        session()->forget('app.feedbacks');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $redirect_to = !empty($redirect) ? redirect()->to($redirect) : '/';
        session_push_alert('success', 'Logged out successfully');
        return redirect()->to($redirect_to);
    }



    // some user and client validation
    private function validate() {
        // returns [success, err_mode, err_data]
        // err_mode [1 => exception, 2 => redirect]

        $url = route_parse_url(request()->fullUrl());
        // https://hris2.localhost.com?mode=dark
        
        if(in_array($url->url, $this->bypassAuthRoutes))
            goto point1;        
        if(in_array($url->path, ['', '/'])) {
            goto point1;
        }
        
        if($this->is_auth) {
            // check account state
            if(!$this->user_info['is_active']) {
                session_push_alert('error', 'Account is deactivated');
                $logout = $this->logout(request(), route('index.index'));
                return [false, 2, $logout];
            } 
            
            // check email verify
            if(!$this->user_info['verify']['email']['is_verified']) {
                if($url->url !== $this->url_verify_email) {
                    session_push_alert('info', 'Please verify your email first');
                    return [false, 2, $this->url_verify_email];
                }
            } else {
                if($url->url === $this->url_verify_email) {
                    session_push_alert('success', 'Account is already verified.');
                    return [false, 2, $this->url_home];
                }
            }
        } else {
            // check if no user
            $is_url_registration = ($url->url === $this->url_register);
            if($this->force_register && config('core.users_count') < 1 && !$is_url_registration) {
                session_push_alert('info', 'Please register first');
                return [false, 2, $this->url_register];
            } 
            elseif($is_url_registration) {
                goto point1;
            }            
            else {
                if($url->url === $this->url_login)
                    goto point1;
                session_push_alert('error', 'Please login first.');
                return [false, 2, $this->url_login];
            }
        }
        point1:
        return [true, null, null];
    }









    public static function getAvatarUrl(string $file, string $theme_mode)
    {
        $url = Storage::disk('user_avatar')->path($file);
        $file_url = CLHF::STORAGE_FileURL($url, 'dispose');

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
        // get user data
        $user = [];
        if(!is_null($id)) {
            $user = \App\Models\User::with([                
                'settings' => function($q) {
                    $q->select(['user_id', 'ac_user_setting.key', 'ac_user_setting.value']);
                },
                // 'theme' => function($q) {
                //     $q->select(['user_id', 'ac_user_theme.theme', 'ac_user_theme.mode']);
                // },
                'state' => function($q) {
                    $q->select(['user_id', 'is_active']);
                },
                'verifyemail' => function($q) {
                    $q->select(['user_id', 'verified_at']);
                },
                'types' => function($q) {
                    $q->leftJoin('ac_role', 'ac_role.id', '=', 'ac_user_type.role_id', );
                    $q->select(['user_id', 'ac_role.id AS role_id', 'ac_role.name', 'ac_role.short']);
                    $q->where(['ac_user_type.is_valid'=>1]);
                    $q->orderBy('ac_user_type.role_id', 'asc');
                },
                'info' => function($q) {
                    $q->select(['user_id', 'ac_user_info.*']);
                },
            ])
            ->where('ac_user.id', '=', $id)
            ->get()->toArr(0)
            // ->toSql()
            ;
            // dd($user);
            
            // harmonize user settings array
            $user_settings = [];
            array_walk($user['settings'], function($v, $k) use(&$user_settings) {
                $user_settings[$v['key']] = $v['value'];
            });            
            $user['settings'] = $user_settings;

            $user['is_active'] = arr_get($user, 'state.is_active', 0) === 1;
            $emailverify = dt_parse(arr_get($user, 'verifyemail.verified_at', ''));
            $user['verify'] = [
                'email' => [
                    'is_verified' => $emailverify->is_valid,
                    'verified_at' => $emailverify->string->onto,
                ],
            ];

            $user['info']['avatar_url'] = SELF::getAvatarUrl($user['info']['avatar'] ?? '', $user['settings']['theme_mode'] ?? 'light');
            $user['name'] = str_sanitize($user['first_name'].' '.$user['last_name']);
        }
        return $user;
    }



    // public static function client_info_(Request $request) {
    //     $has_tz = $request->has('_timezone');
    //     $tz_before = $request->input('_timezone');

    //     $request->merge(['_timezone'=>config('env.APP_TIMEZONE')]);  // override timezone
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
    public static function client_info_(Request $request) {
        // $request->merge(['_timezone'=>config('env.APP_TIMEZONE')]);  // override timezone
        $client_info = WebClient::getClientUAInfo($request);  // get client info
        return $client_info;
    }




    public function redirect(bool $goto_intended_url, bool $return_object=true) {
        // does not check is_valid
        // @param $return_object ? redirect_object : route_string        
        $user_role_ids = array_column(config('user.types'), 'id');
        
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
            $next_url = $next_url === config('env.APP_URL') ? $default : $next_url;
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




    
    public function user_menu(Request $request, bool $strict = true)
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
            'jappl',
            'admin',
        ];
        array_unique($show_roles);

        // get the menu of each role
        $main = [];
        array_push($main, config('core.menu.all'));
        if(cuser_is_auth()) {
            foreach($show_roles as $k1=>$v1) {
                $lbl = [false, ''];
                foreach(config('user.types') as $k2=>$v2) {
                    if($v2['short'] === $v1) {
                        $lbl = [true, $v2['name']];
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
                $m = config('core.menu.'.$v1);
                if(!empty($m)) {
                    array_unshift($m, $category($lbl[1]));
                    array_push($main, $m);
                }
            }
            array_push($main, $category('User'), config('core.menu.user'));
        } else {
            array_push($main, $category('Guest'), config('core.menu.guest'));
        }

        // remove empty elements
        $main2 = [];
        foreach($main as $k1=>$v2) {
            if(!empty($v2)) $main2[] = $v2;
        }

        return [
            'documentation' => $this->user_menu_documentation($request),
            'horizontal' => $this->user_menu_horizontal($request),
            // 'main' => $this->user_menu_main($request),
            'main' => $main2,
        ];
    }



    private function user_menu_main(Request $request)
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
        // dd($data);
        return $data;
    }



    private function user_menu_documentation(Request $request)
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
