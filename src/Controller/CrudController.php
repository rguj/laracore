<?php

namespace Rguj\Laracore\Controller;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;
use Exception;
// use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Carbon;
// use Illuminate\Support\Facades\Request;
// use Illuminate\Foundation\Http\FormRequest;
// use App\Models\User;
use Illuminate\Support\Str;

// use Backpack\CRUD\app\Library\CrudPanel\CrudPanel as CRUD;
// use Rguj\Laracore\Library\LBP\CrudPanel\CrudPanel as CRUD2;

use \Illuminate\Foundation\Bus\DispatchesJobs;
use \Illuminate\Foundation\Validation\ValidatesRequests;
use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
// use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;

// use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

# custom operations
use Rguj\Laracore\Traits\LBP\Operations\ListOperation;
use Rguj\Laracore\Traits\LBP\Operations\FetchOperation;

// use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
// use App\Traits\CrudPanel2;
use Rguj\Laracore\Library\LBP\CrudPanel\CrudPanel2;

// use Rguj\Laracore\Provider\BackpackServiceProvider;
// use Illuminate\Container\Container;

class CrudController extends Controller
{
    use DispatchesJobs, ValidatesRequests;

    use
        ShowOperation
        , ListOperation
        , CreateOperation
        , UpdateOperation
        , DeleteOperation
        // , ReorderOperation

        , FetchOperation
    ;


    // /*
    //  * @var \Rguj\Laracore\Library\LBP\CrudPanel\CrudPanel
    //  * @var \Backpack\CRUD\app\Library\CrudPanel\CrudPanel
    //  */

    /** @var \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud */    
    public $crud;

    /** @var array $data */ 
    public $data = [];
    
    /** @var \Rguj\Laracore\Library\LBP\CrudPanel\CrudPanel2 $crud2 */  
    public $crud2;

    # custom

    public string $role_model;
    public string $permission_model;

    /** @var \Illuminate\Support\Carbon $now_at     */ public $now_at = null;
    /** @var string $now_at_dtu     `Y-m-d H:i:s.u` */ public string $now_at_dtu = '';
    /** @var string $now_at_dt      `Y-m-d H:i:s`   */ public string $now_at_dt = '';
    /** @var string $now_at_date    `Y-m-d`         */ public string $now_at_date = '';
    /** @var string $now_at_timeu    `H:i:s`        */ public string $now_at_timeu = '';
    /** @var string $now_at_time    `H:i:s`         */ public string $now_at_time = '';

    // public array $defaultRadioOptions = [
    //     0 => "X",
    //     1 => "✓",
    // ];




    

    final public function __construct()
    {
        if(!$this->crud2) {
            $this->crud2 = new CrudPanel2($this->crud);
        }

        if ($this->crud) {
            return;
        }

        # custom
        // PARENT::__construct();
        $this->now_at = Carbon::now();
        $this->now_at_dtu = $this->now_at->format('Y-m-d H:i:s.u');
        $this->now_at_dt = $this->now_at->format('Y-m-d H:i:s');
        $this->now_at_date = $this->now_at->format('Y-m-d');
        $this->now_at_timeu = $this->now_at->format('H:i:s.u');
        $this->now_at_time = $this->now_at->format('H:i:s');


        // ---------------------------
        // Create the CrudPanel object
        // ---------------------------
        // Used by developers inside their ProductCrudControllers as
        // $this->crud or using the CRUD facade.
        //
        // It's done inside a middleware closure in order to have
        // the complete request inside the CrudPanel object.

        $this->middleware(function ($request, $next) {
            /** @var \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud */    
            $this->crud = app('crud');
            $this->crud->setRequest($request);
            $this->setupDefaults();
            $this->setup();
            $this->setupConfigurationForCurrentOperation();


            # ----------------
            # CUSTOM

            // $this->crud2 = new CrudPanel2($this->crud);
            $this->crud->addButton('top', 'refresh2', 'view', 'Refresh');
            // $this->crud->addButton('top', 'reset2', 'view', 'Reset');
            if(!$this->crud->hasOperationSetting('exportButtonShow')) {
                $this->crud2->enableColumnVisibilityButton();
                $this->crud2->enableExportButtons(true);

                $this->crud2->__setOperationSetting('now_at', $this->now_at);
                $this->crud2->__setOperationSetting('now_at_dtu', $this->now_at_dtu);
                $this->crud2->__setOperationSetting('now_at_dt', $this->now_at_dt);
                $this->crud2->__setOperationSetting('now_at_date', $this->now_at_date);
                $this->crud2->__setOperationSetting('now_at_timeu', $this->now_at_timeu);
                $this->crud2->__setOperationSetting('now_at_time', $this->now_at_time);
            }

            return $next($request);
        });

        
        


    }

    /**
     * Allow developers to set their configuration options for a CrudPanel.
     * 
     * @return void
     */
    public function setup()
    {


    }

    /**
     * Load routes for all operations.
     * Allow developers to load extra routes by creating a method that looks like setupOperationNameRoutes.
     *
     * @param  string  $segment  Name of the current entity (singular).
     * @param  string  $routeName  Route name prefix (ends with .).
     * @param  string  $controller  Name of the current controller.
     */
    public function setupRoutes($segment, $routeName, $controller)
    {
        preg_match_all('/(?<=^|;)setup([^;]+?)Routes(;|$)/', implode(';', get_class_methods($this)), $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                $this->{'setup'.$methodName.'Routes'}($segment, $routeName, $controller);
            }
        }
    }

    /**
     * Load defaults for all operations.
     * Allow developers to insert default settings by creating a method
     * that looks like setupOperationNameDefaults.
     */
    protected function setupDefaults()
    {
        preg_match_all('/(?<=^|;)setup([^;]+?)Defaults(;|$)/', implode(';', get_class_methods($this)), $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                $this->{'setup'.$methodName.'Defaults'}();
            }
        }
    }

    /**
     * Load configurations for the current operation.
     *
     * Allow developers to insert default settings by creating a method
     * that looks like setupOperationNameOperation (aka setupXxxOperation).
     */
    protected function setupConfigurationForCurrentOperation()
    {
        $operationName = $this->crud->getCurrentOperation();
        if (! $operationName) {
            return;
        }

        $setupClassName = 'setup'.Str::studly($operationName).'Operation';

        /*
         * FIRST, run all Operation Closures for this operation.
         *
         * It's preferred for this to closures first, because
         * (1) setup() is usually higher in a controller than any other method, so it's more intuitive,
         * since the first thing you write is the first thing that is being run;
         * (2) operations use operation closures themselves, inside their setupXxxDefaults(), and
         * you'd like the defaults to be applied before anything you write. That way, anything you
         * write is done after the default, so you can remove default settings, etc;
         */
        $this->crud->applyConfigurationFromSettings($operationName);

        /*
         * THEN, run the corresponding setupXxxOperation if it exists.
         */
        if (method_exists($this, $setupClassName)) {
            $this->{$setupClassName}();
        }
    }







    // public function setupListOperation()
    // {
        
    // }

    // public function setupCreateOperation()
    // {
        
    // }

    // public function setupUpdateOperation()
    // {
        
    // }

    // public function setupDeleteOperation()
    // {
        
    // }

    /*
     * Get an array list of all available guard types
     * that have been defined in app/config/auth.php
     *
     * @return array
     **/
    private function getGuardTypes()
    {
        $guards = config('auth.guards');

        $returnable = [];
        foreach ($guards as $key => $details) {
            $returnable[$key] = $key;
        }

        return $returnable;
    }












    # ------------------------------------
    # CUSTOM

}
