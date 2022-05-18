<?php

namespace Rguj\Laracore\Library;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Libraries\AppFn;
use App\Libraries\DT;
use App\Libraries\WebClient;
use App\Libraries\CLHF;


/**
 * Global class for global constants and functions
 */


class Prologue {
    
    public function __construct()
    {
        
    }


    public function roles()
    {
        $arr1 = DB::table('user_roles')->where(['is_valid'=>1]);


    }



}












