<?php

namespace Rguj\Laracore\Provider;

use Exception;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;

use Rguj\Laracore\Middleware\ClientInstanceMiddleware;
use App\Core\Adapters\Theme;
use Rguj\Laracore\Macro\EloquentCollectionMacro;

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
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(\App\Providers\TelescopeServiceProvider::class);
        }

        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;
        
        if ($app->isLocal()) {
            $app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
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
        // auth()->user();  // trigger eloquent object
        $this->checkRequirement();
        $this->addMacros();
        $this->addBladeDirectives();
        // $this->initializeMetronic();  // gone to HttpResponse::getView()
        $this->addSequence();

        if(env('FORCE_HTTPS')) {
            URL::forceScheme('https');
        }

        // dd(theme()->getMenu());
        if(!app()->runningInConsole() && request()->ip() !== '192.168.5.250') {
            dd('Under maintenance');
        }

        // for($x=1; $x<=24; $x++) {
        //     DB::table('role_permission')->updateOrInsert(['role_id'=>1, 'permission_id'=>$x]);
        // }
        // dd(5353);
        
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
    
            if (isRTL()) {
                // RTL html attributes
                Theme::addHtmlAttribute('html', 'dir', 'rtl');
                Theme::addHtmlAttribute('html', 'direction', 'rtl');
                Theme::addHtmlAttribute('html', 'style', 'direction:rtl;');
                Theme::addHtmlAttribute('body', 'direction', 'rtl');
            }
        }
    }


    protected function addSequence()
    {
        $tbl_user = db_model_table_name(\App\Models\User::class);
        $tbl_user_state = db_model_table_name(\App\Models\UserState::class);
        $defaultDBConn = (string)config('database.default');

        $bool1 = (
            !empty($defaultDBConn)
            && Schema::connection($defaultDBConn)->hasTable($tbl_user)
            && Schema::connection($defaultDBConn)->hasTable($tbl_user_state)
        );
        if(!$bool1) {
            $e = 'Your database may be empty. Please check and use `migrate`.';
            if(app()->runningInConsole()) {
                dump($e);
            } else {
                dd($e);
            }
            return false;
        }

        // dd(DB::table('ccms'));

        // COUNT ACTIVE USERS
        config_unv_set('users_count', DB::table($tbl_user)->join($tbl_user_state, $tbl_user_state.'.user_id', '=', $tbl_user.'.id')->where($tbl_user_state.'.is_active', '=', 1)->count($tbl_user.'.id'));
        
        // SET REGISTER NOW
        config_unv_set('register_now', config_unv('users_count') <= 0);

        // GET ROLES
        $db_roles = \App\Models\Role::where(['is_valid'=>1])->get()->toArr();
        config_unv_set('roles', $db_roles);


        // CHECK CLIENT_INSTANCE ROLES
        $cls1 = new \ReflectionClass(ClientInstanceMiddleware::class);
        $consts = $cls1->getConstants();
        $arr2 = [];
        foreach($db_roles as $k=>$v) {
            $arr2[strtoupper('ROLE_'.$v['short'])] = $v['id'];
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




}
