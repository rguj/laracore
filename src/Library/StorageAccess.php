<?php
namespace Rguj\Laracore\Library;

//use Illuminate\Http\Request;
use Rguj\Laracore\Request\Request;
use Illuminate\Support\Str;

use Exception;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\CLHF;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\HttpResponse;
use Rguj\Laracore\Library\WebClient;

/**
 * Storage Access Functions
 * 
 */
class StorageAccess {

    //public $class1;


    public function __construct() {
        // CLASS INSTANCE
        
    }


    public static function data_student(int $user_id)
    {
        $ret = [false, 0, ''];  // success, sid, md5_sid
        $cls = 'App\Http\Controllers\Student\LinkController';
        if(!class_exists($cls) || !method_exists($cls, 'FETCH_LinkedStudID'))
            goto point1;        
        $sid = (new ($cls))->FETCH_LinkedStudID($user_id) ?? 0;
        $ret[0] = $sid > 0;
        $ret[1] = $ret[0] ? $sid : 0;
        $ret[2] = $ret[0] ? md5($sid) : '';
        point1:
        return $ret;
    }


    public static function check(Request $request, $file) {
        // validate by directory or by user_id     

        $uid = cuser_id();

        $student = SELF::data_student($uid);
        $student_id = $student[0];
        $sid_md5 = $student[2];

        $is_admin = cuser_is_admin();
        $is_rstaff = cuser_is_rstaff();
        $is_eofficer = cuser_is_eofficer();
        $is_cashier = cuser_is_cashier();
        $is_student = cuser_is_student();

        // $is_self_file = $file['basename'] === $sid_md5;
        $is_self_file = !empty($file['basename']) && Str::contains($file['basename'], $sid_md5);
        $can_stud_photo = ($is_admin || $is_rstaff || $is_eofficer || $is_cashier || $is_cashier);

        return match($file['dir_app']) {
            'stud_photo' => ($is_self_file || $can_stud_photo),
            'stud_esig'  => ($is_self_file || $can_stud_photo),
            'stud_loa'   => ($is_self_file || $can_stud_photo),
            'stud_brcf'  => ($is_self_file || $can_stud_photo),
            'stud_gomo'  => ($is_self_file || $can_stud_photo),
            'stud_pocl'  => ($is_self_file || $can_stud_photo),
            'stud_grad'  => ($is_self_file || $can_stud_photo),
            'stud_paym'  => ($is_self_file || $can_stud_photo),
            'stud_vacc'  => ($is_self_file || $can_stud_photo),
            default => false,
        };
    }
   

}


