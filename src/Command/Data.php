<?php

namespace Rguj\Laracore\Command;

use Illuminate\Console\Command;

# -----------------------------
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\General\ClientInstanceMiddleware;
use App\Models\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

# -----------------------------



class Data extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets data in db';



    public \Carbon\Carbon $dt_now;
    public string $dt_now_str;
    public array $timestamps;

    public array $allAdminPermissions = [];

    public array $roles = [
        [1, 'admin', 'Administrator'],
        [2, 'rstaff', 'Registrar Staff'],
        [3, 'eofficer', 'Enrollment Officer'],
        [4, 'student', 'Student'],
        [5, 'cashier', 'Cashier'],
        [6, 'cstaff', 'Clinic Staff'],
        [7, 'jappl', 'Job Applicant'],
    ];




    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->dt_now = dt_now();
        $this->dt_now_str = $this->dt_now->format(dt_standard_format());
        $this->timestamps = ['created_at'=>$this->dt_now_str, 'updated_at'=>$this->dt_now_str];

        $this->allAdminPermissions = array_merge(
            $this->allAdminPermissions, 
            $this->adminPermissions,
            $this->authPermissions,
        );
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // dd();
        foreach($this->truncateTables as $k=>$v) {
            if(!str_starts_with($v, 'pl_')) {
                DB::table($v)->truncate();
            }
        }

        $request = request();

        $this->resetPermissions($request);
        $this->resetRoles($request);
        $this->resetUsers($request);
        

        $this->line('<fg=green>Data reset complete!</fg=green>');
    }


    




    

    public function mergeTimestamp(array $data)
    {
        return array_merge($data, $this->timestamps);
    }

    public function resetPermissions(Request $request)
    {
        Permission::truncate();        
        app()[PermissionRegistrar::class]->forgetCachedPermissions();  // Reset cached roles and permissions

        $permissions = [];
        foreach($this->adminPermissions as $permission) {
            $permissions[] = $this->mergeTimestamp(['name' => $permission, 'guard_name' => 'web', 'is_valid' => 1]);
        }
        Permission::insert($permissions);
    }

    public function resetRoles(Request $request)
    {
        Role::truncate();        
        $roles = [];
        foreach($this->roles as $v) {
            $roles[] = $this->mergeTimestamp(['id' => $v[0], 'name'=>$v[2], 'short'=>$v[1], 'guard_name'=>'web', 'is_valid'=>1]);
        }
        Role::insert($roles);
        Role::findByName('Administrator')->givePermissionTo($this->adminPermissions);        
    }

    public function resetUsers(Request $request)
    {
        User::truncate();

        $user = User::create([
            'email' => 'slimyslime777@gmail.com',
            'password' => Hash::make('pa55123!!'),
            'last_name' => 'Slime',
            'first_name' => 'Slimy',
        ]);
        
        RegisteredUserController::__callNonStatic('systemVerifyEmail', [$request, $user, dt_now()]);
        RegisteredUserController::__callNonStatic('initiateUser', [$request, $user]);

        /** @var \Spatie\Permission\Models\Permission $user */
        $user->assignRole(array_column($this->roles, 2));

        //event(new Registered($user));
    }

    public function resetMenu(Request $request)
    {

    }



    public $roleMenus = [

        'auth' => [
            'is_middleware' => true,
            'is_role' => false,
            'role_id' => null,
            'value' => [],
        ],
        
        'jappl' => [
            'is_middleware' => false,
            'is_role' => true,
            'role_id' => ClientInstanceMiddleware::ROLE_JAPPL,
            'value' => [],
        ],
        
        'admin' => [
            'is_middleware' => false,
            'is_role' => true,
            'role_id' => ClientInstanceMiddleware::ROLE_ADMIN,
            'value' => [],
        ],


    ];


    public $adminPermissions = [
        'users_manage',

        'admin.permissions.index',
        'admin.permissions.store',
        'admin.permissions.create',
        'admin.permissions.show',
        'admin.permissions.update',
        'admin.permissions.destroy',
        'admin.permissions.edit',

        'admin.roles.index',
        'admin.roles.store',
        'admin.roles.create',
        'admin.roles.show',
        'admin.roles.update',
        'admin.roles.destroy',
        'admin.roles.edit',

        'admin.users.index',
        'admin.users.store',
        'admin.users.create',
        'admin.users.show',
        'admin.users.update',
        'admin.users.destroy',
        'admin.users.edit',

        'admin.menu.index',
        'admin.menu.store',
        'admin.menu.create',
        'admin.menu.show',
        'admin.menu.update',
        'admin.menu.destroy',
        'admin.menu.edit',
    ];

    public $authPermissions = [
        'index.index',
        'index.store',
    ];


    public $truncateTables = [
        //'ac_activity_log',
        //'ac_failed_job',
        //'ac_migration',
        'ac_model_permission',
        'ac_model_role',
        'ac_password_reset',
        'ac_permission',
        'ac_role',
        'ac_role_permission',
        'ac_user',
        'ac_user_info',
        'ac_user_setting',
        'ac_user_state',
        'ac_user_type',
        'ac_verify_email',
        'ac_verify_facebook',
        'ac_verify_google',
        'ad_birthdate',
        'ad_company',
        'ad_coursesdegree',
        'ad_disability',
        'ad_email',
        'ad_fname',
        'ad_lname',
        'ad_mname',
        'ad_mobilenumber',
        'ad_occupation',
        'ad_place_cm',
        'ad_place_d',
        'ad_place_ps',
        'ad_relation',
        'ad_zipcode',
        'jappl_addr_curr',
        'jappl_addr_emgn',
        'jappl_addr_home',
        'jappl_cred_brcf',
        'jappl_cred_gomo',
        'jappl_cred_grad',
        'jappl_cred_paym',
        'jappl_cred_pocl',
        'jappl_fami_pare',
        'jappl_fami_sibl',
        'jappl_fami_sibl_',
        'jappl_fami_spou',
        'jappl_pers_deta',
        'jappl_pers_disa',
        'jappl_pers_disa_',
        'jappl_pers_esig',
        'jappl_pers_phot',
        // 'pl_birthsex',
        // 'pl_civilstatus',
        // 'pl_country',
        // 'pl_disability',
        // 'pl_empstat',
        // 'pl_hea',
        // 'pl_namex',
        // 'pl_nationality',
        // 'pl_place_ph',
        // 'pl_religion',
        // 'pl_type_ahsb',
        // 'pl_type_cm',
        // 'pl_type_crsdeg',
        // 'pl_type_ps',
    ];




}
