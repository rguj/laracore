<?php

namespace Rguj\Laracore\Library;


use Illuminate\Http\Request;
use Illuminate\Support\Str;


use Exception;
use App\Libraries\AppFn;
use App\Libraries\DT;
use App\Libraries\WebClient;
use App\Libraries\CLHF;

use App\Http\Controllers\Student\Link as StudentLink;

class StorageAccess {

    //public $class1;


    public function __construct() {
        // CLASS INSTANCE
        
    }


    public static function check(Request $request, $file) {
        // validate by directory or by user_id     

        $uid = CLHF::AUTH_UserID();
        $uid_md5 = md5($uid);
        $student_id = StudentLink::FETCH_LinkedStudID($uid) ?? 0;
        $sid_md5 = md5($student_id);

        switch($file['dir_app']) {

            case 'stud_photo':
                return ($file['basename'] === $sid_md5 || CLHF::AUTH_RoleAuthorized([1,2,3,5], $uid));
                break;

            case 'stud_esig':
                return ($file['basename'] === $sid_md5 || CLHF::AUTH_RoleAuthorized([1,2,3,5], $uid));
                break;
 
            case 'stud_loa':
                return (Str::contains($file['basename'], $sid_md5) || CLHF::AUTH_RoleAuthorized([1,2,3,5], $uid));
                break;
            
            case 'stud_brcf':
                return (Str::contains($file['basename'], $sid_md5) || CLHF::AUTH_RoleAuthorized([1,2,3,5], $uid));
                break;
                    
            case 'stud_gomo':
                return (Str::contains($file['basename'], $sid_md5) || CLHF::AUTH_RoleAuthorized([1,2,3,5], $uid));
                break;
                
            case 'stud_pocl':
                return (Str::contains($file['basename'], $sid_md5) || CLHF::AUTH_RoleAuthorized([1,2,3,5], $uid));
                break;
                
            case 'stud_grad':
                return (Str::contains($file['basename'], $sid_md5) || CLHF::AUTH_RoleAuthorized([1,2,3,5], $uid));
                break;
                
            case 'stud_paym':
                return (Str::contains($file['basename'], $sid_md5) || CLHF::AUTH_RoleAuthorized([1,2,3,5], $uid));
                break;

            default:
                return false;
                break;
            
        }
    }
   

}


