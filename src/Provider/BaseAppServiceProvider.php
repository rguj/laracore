<?php

namespace Rguj\Laracore\Provider;

use Exception;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;

use Rguj\Laracore\Middleware\ClientInstanceMiddleware;
use App\Core\Adapters\Theme;
use Rguj\Laracore\Macro\EloquentCollectionMacro;
use Rguj\Laracore\Library\WebClient;
use Rguj\Laracore\Request\Request;

class BaseAppServiceProvider extends ServiceProvider

{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
			if(class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
				$this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
			}
			/*if(class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
				$this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
			}
            $this->app->register(\App\Providers\TelescopeServiceProvider::class);*/
        }

        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;

        if ($app->isLocal()) {
			if(class_exists(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class)) {
				$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
			}
            //$app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }


        // Load BaseHelper
        // require_once base_path('/app/Helper/BaseHelper.php');
        require_once base_path('vendor/rguj/laracore/src/Helper/BaseHelper.php');

        # --------------------
        # CUSTOM

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Artisan::call('view:clear');
        // auth()->user();  // trigger eloquent object
        $this->checkRequirement();
        $this->addMacros();
        $this->addBladeDirectives();
        // $this->initializeMetronic();  // gone to HttpResponse::getView()
        $this->addSequence();

    }




    protected function checkRequirement()
    {
        //$required_php = (string)env('PHP_MIN_VERSION', '8.1.2');
        //$required_laravel = (string)env('LARAVEL_MIN_VERSION', '9.5.1');

		$composer = json_decode(file_get_contents(base_path('./composer.json')), true);
		$getMinVersion = function(string $key, string $default) use($composer) {
			return preg_replace("/[^0-9\.]/", '', trim((string)arr_get($composer, 'require.'.$key, $default)));
		};

		$required_php = $getMinVersion('php', '8.0');
        $required_laravel = $getMinVersion('laravel/framework', '8.12');

        if(!app()->runningInConsole()) {

            // check php version
			if(empty($required_php)) {
				die('Required PHP version must not be empty.');
			}
            if(!str_version_ge(PHP_VERSION, $required_php)) {
                die('PHP version must be '.$required_php.' or up.');
            }

            // check laravel version
			if(empty($required_laravel)) {
				die('Required Laravel version must not be empty.');
			}
            if(!str_version_ge(app()->version(), $required_laravel)) {
                die('Laravel version must be '.$required_laravel.' or up.');
            }

            // $this->evalBrowser();

            // check config files
            $check_configs = [
                'z.base',
                'z.browser',
                'z.role',
            ];
            foreach($check_configs as $k=>$v) {
                if(!config()->has($v)) {
                    die('Missing config file: '.$v);
                }
            }
        }


    }



    protected function addMacros()
    {
        // QUERY BUILDER
        \Illuminate\Database\Query\Builder::macro('toArr', function () {
            /** @var \Illuminate\Database\Query\Builder $this */
            return json_decode(json_encode($this->get()->toArray()), true);
        });


        // ELOQUENT BUILDER
        \Illuminate\Database\Eloquent\Builder::macro('toArr',
        // use get() or first() before calling this, except for auth()->user()
        function () {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return json_decode(json_encode($this->model->toArray()), true);
        });

        \Illuminate\Database\Query\Builder::macro('toRawSql', function(){
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return array_reduce($this->getBindings(), function($sql, $binding){
                return preg_replace('/\?/', is_numeric($binding) ? $binding : "'".$binding."'" , $sql, 1);
            }, $this->toSql());
        });

        \Illuminate\Database\Eloquent\Builder::macro('arrGet',
        function ($key, $default = null) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $ths = $this;
            if(!is_array($ths)) throw new \Exception('$this must be array');
            return arr_get($ths, $key, $default);
        });

        // ELOQUENT COLLECTION
        // \Illuminate\Database\Eloquent\Collection::macro('arrGet', function ($key, $default = null) {
        //     // dd(2);
        //     if(!is_array($this)) throw new \Exception('$this must be array');
        //     return arr_get($this, $key, $default);
        // });
        // \Illuminate\Database\Eloquent\Collection::macro('toArr', function () {
        //     return json_decode(json_encode($this), true);
        // });
        \Illuminate\Database\Eloquent\Collection::mixin(new EloquentCollectionMacro);

    }

    protected function addBladeDirectives()
    {
        Blade::directive('blade_error', function($expression) {  // blade render attr errors (brae)
            return "<?php echo blade_error($expression); ?>";
        });

        Blade::directive('blade_purpose', function($expression){
            return "<?php echo blade_purpose($expression); ?>";
        });

        Blade::directive('blade_route', function($expression){
            return "<?php echo blade_route($expression); ?>";
        });

    }

    public static function initializeMetronic()
    {
        $theme = null;
        try { $theme = theme(); } catch(\Throwable $ex) {}

        if(config()->has('demoa') && get_class($theme) === 'App\Core\Adapters\Theme') {
            $theme = theme();

            // Share theme adapter class
            View::share('theme', $theme);

            // Set demo globally
            // $theme->setDemo(request()->input('demo', 'demo1'));
            $theme->setDemo('demoa');

            $theme->initConfig();

            bootstrap()->run();

            if (isRTL() && class_exists('App\Core\Adapters\Theme')) {
                // RTL html attributes
                // Theme::addHtmlAttribute('html', 'dir', 'rtl');
                // Theme::addHtmlAttribute('html', 'direction', 'rtl');
                // Theme::addHtmlAttribute('html', 'style', 'direction:rtl;');
                // Theme::addHtmlAttribute('body', 'direction', 'rtl');
            }
        }
    }


    protected function addSequence()
    {
        // FORCE SCHEME

        if(config_env('APP_FORCE_HTTPS', false)) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }

        $tbl_user = db_model_table_name(\App\Models\User::class);
        //$tbl_user_state = db_model_table_name(\App\Models\UserState::class);
        $defaultDBConn = (string)config('database.default');

        $bool1 = (
            !empty($defaultDBConn)
            && Schema::connection($defaultDBConn)->hasTable($tbl_user)
            //&& Schema::connection($defaultDBConn)->hasTable($tbl_user_state)
        );
        if(!$bool1) {
            $e = 'Your database may be empty. Please check and use migrate command. Connection: '.$defaultDBConn;
            if(app()->runningInConsole()) {
                dump($e);
            } else {
                dd($e);
            }
            return false;
        }

        // dd(DB::table('ccms'));

        // COUNT ACTIVE USERS
        //config_unv_set('users_count', DB::table($tbl_user)->join($tbl_user_state, $tbl_user_state.'.user_id', '=', $tbl_user.'.id')->where($tbl_user_state.'.is_active', '=', 1)->count($tbl_user.'.id'));
		config_unv_set('users_count', DB::table($tbl_user)->where($tbl_user.'.activated_at', 'IS NOT', null)->count($tbl_user.'.id'));

        // SET REGISTER NOW
        config_unv_set('register_now', config_unv('users_count') <= 0);

        // GET ROLES
        //$db_roles = \App\Models\Role::where(['is_valid'=>1])->get()->toArr();
        $db_roles = \App\Models\Role::get()->toArr();
        config_unv_set('roles', $db_roles);


        // CHECK CLIENT_INSTANCE ROLES
        $cls1 = new \ReflectionClass(ClientInstanceMiddleware::class);
        $consts = $cls1->getConstants();
        $arr2 = [];
        foreach($db_roles as $k=>$v) {
            $arr2[strtoupper('ROLE_'.$v['name'])] = $v['id'];
        }
        foreach($consts['ROLES'] as $k=>$v) {
            $c = strtoupper('ROLE_'.$v[1]);
            if(!array_key_exists($c, $consts))
                throw new Exception("Missing constant role: $v[1]");
            if($consts[$c] !== $v[0])
                throw new Exception("Role value mismatch in constant: $v[1]");
            if(!array_key_exists($c, $arr2))
                throw new Exception("Missing DB role: $v[1]");
            if($arr2[$c] !== $v[0])
                throw new Exception("Role value mismatch in DB: $v[1]");
        }

        // VALIDATE DB VALUES
        // foreach($db_roles as $key=>$val) {
        //     $const1 = strtoupper('ROLE_'.$val['short']);
        //     if(!array_key_exists($const1, $constants1) || $constants1[$const1] !== $val['id'])
        //         throw new \Exception('Missing DB role (seq #'.$key.')');
        // }


    }













    /*protected function evalBrowser(bool $skipRootPath = true)
    {
        $skip = [
            '/',
            'check/iops',
        ];

        $ua = WebClient::__getUA();
        // config()->set('browser', []);

        // if($skipRootPath && request()->path() === '/') {
        if($skipRootPath && in_array(request()->path(), $skip)) {
            goto point2;
        }

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
                $ba = config('browser.requirement');

                if(!array_key_exists($ua['device']['type'], $ba)) {
                    config()->set('browser.is_valid_type', false);
                    throw new exception('Invalid device type');
                }
                config()->set('browser.type', $ua['device']['type']);

                if(!array_key_exists($ua['browser']['name'], $ba[$ua['device']['type']])) {
                    config()->set('browser.is_valid_name', false);
                    throw new exception('Invalid device name');
                }
                config()->set('browser.name', $ua['browser']['name']);

                $version_required = $ba[$ua['device']['type']][$ua['browser']['name']];
                $version_current = $ua['browser']['version'];
                config()->set('browser.version.required', $version_required);
                config()->set('browser.version.current', $version_current);

                // $flagged = false;
                if(str_version_compare($version_current, $version_required, '<')) {
                    config()->set('browser.is_outdated', true);
                    $flagged = true;
                }

                $http_referrer = $_SERVER['HTTP_REFERER'] ?? '';
                $allowed_host = (array)config('browser.allowed_host');
                $blocked_host = (array)config('browser.blocked_host');
                $up = url_parse($http_referrer);

                if(
                    // in_array($http_referrer, $block_referrer, true)
                    ($up->is_valid && in_array($up->host, $blocked_host, true))
                    || ($up->is_valid && !in_array($up->host, $allowed_host, true))
                    || (!str_empty($http_referrer) && !$up->is_valid)
                ) {
                    config()->set('browser.is_blocked_referrer', true);
                    $flagged = true;
                }

                if($flagged) {
                    if(!config('browser.is_valid_type')) {
                        throw new exception('Unsupported referrer');
                    }
                    if(!config('browser.is_valid_name')) {
                        throw new exception('Unsupported referrer');
                    }
                    if(!config('browser.is_outdated')) {
                        throw new exception('Unsupported browser');
                    }
                    if(!config('browser.is_blocked_referrer')) {
                        throw new exception('Unsupported referrer');
                    }
                }

            } catch(\Exception $ex) {
                $err_msg = $ex->getMessage();

                if(!in_array($ua['device']['name'] ?? '', (array)config('browser.bypass_device_name'))) {
                    config()->set('browser.is_valid', false);
                    config()->set('browser.err_msg', $err_msg);
                    abort(response()->view('errors.unsupported-browser', config('browser'), 406));
                }

            }
            point1:

        };
        $vb();
        point2:
        config()->set('browser.useragent', $ua);

        // updatemybrowser.org minumum requirements
        $umb = [];
        $device_type = (string)config('browser.useragent.device.type');
        $req = !empty($device_type) ? (array)config('browser.requirement.'.$device_type) : [];
        foreach($req as $k=>$v) {
            $umb[$k] = (float)$v;
        }
        config()->set('browser.umb', $umb);


    }*/


}
