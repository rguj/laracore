<?php

namespace Rguj\Laracore\Library;


use Exception;
use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\CLHF;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\StorageAccess;
use Rguj\Laracore\Library\WebClient;

class FieldRules {

    /*public static $func_call = [
        'general'         => 'getGeneral',
        'sis_personal'    => 'getSISPersonal',
        'sis_addr_curr'   => 'getSISAddrCurr',
        'sis_addr_emgn'   => 'getSISAddrEmgn',
        'sis_addr_home'   => 'getSISAddrHome',
        'sis_addr'        => 'getSISAddr',

    ];*/



    public function __construct() {
        
    }

    /*public static function get(string $target_func='') {
        if(AppFn::STR_IsBlankSpace($target_func)) {
            foreach(SELF::$func_call as $key=>$val) {
                $output[$key] = SELF::{$val}();
            }
        } else {
            $is_found = false;
            foreach(SELF::$func_call as $key=>$val) {
                if($target_func === $key) {
                    $is_found = true;
                    break;
                }
            }
            if($is_found !== true)
                throw new Exception('Field rule `'.$target_func.'` not found');
            $output = SELF::{SELF::$func_call[$target_func]}();
        }
        return $output;
    }*/

































    public static function getRegisterStudent() {
        $FR_Auth = FieldRules::getGeneral();
        
        $rules = [
            /*'lname' => [
                'min' => $FR_SIS['lname']['min'],
                'max' => $FR_SIS['lname']['max'],
                'regex' => $FR_SIS['lname']['regex'],
            ],
            'fname' => [
                'min' => $FR_SIS['fname']['min'],
                'max' => $FR_SIS['fname']['max'],
                'regex' => $FR_SIS['fname']['regex'],
            ],
            'mname' => [
                'min' => $FR_SIS['mname']['min'],
                'max' => $FR_SIS['mname']['max'],
                'regex' => $FR_SIS['mname']['regex'],
            ],
            'namex' => [
                'min' => $FR_SIS['namex']['min'],
                'max' => $FR_SIS['namex']['max'],
            ],*/

            'email' => [
                'min' => $FR_Auth['email']['min'],
                'max' => $FR_Auth['email']['max'],
                'regex' => $FR_Auth['email']['regex'],
            ],
            'password' => [
                'min' => $FR_Auth['password']['min'],
                'max' => $FR_Auth['password']['max'],
                'regex' => $FR_Auth['password']['regex'],
            ],
        ];
        $rules['password_confirmation'] = $rules['password'];
        //dd($rules);
        return $rules;
    }


    public static function getGeneral() {  # General / Common Field Rules
        # CONFIGS
        $APP_DEV_MODE = AppFn::CONFIG_env('APP_DEV_MODE', false, 'boolean');
        $pw_min_standard = 8;
        $pw_min = $APP_DEV_MODE ? 3 : $pw_min_standard;
        $pw_max = 50;

        $ks = AppFn::STR_GetKeyboardSymbols(true);

        $arr1 = [
            'pname' => [  # Person Name
                'min' => 1,
                'max' => 255,
                //'regex' => '/^(?:[\p{L}\p{Mn}\p{Pd}\'\x{2019}]+\s?)+$/u',
                'regex' => '/^(?:[\p{L}\p{Mn}\p{Pd}\'\x{2019}]+[\.]?[\s]?)+$/u',
                    # one space, single quote, Unicode letters(diacritics and more)
            ],
            'function' => [
                'regex' => '/[A-Za-z_]{1}[A-Za-z0-9_]*/u',
            ],
            'auth_with' => [
                'min' => 3,
                'max' => 255,
                'regex' => '/^([A-Za-z0-9]+_[A-Za-z0-9@+\-_~.]+)$/u',
            ],
            'email' => [
                'min' => 3,
                'max' => 255,
                'regex' => '/^([A-Za-z0-9_]+){1}([\.]?[A-Za-z0-9_]+)*([@]){1}(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/u',
            ],
            'password' => [
                'min' => $pw_min,
                'max' => $pw_max,
                //'regex' => '/^([A-Za-z0-9'.(!$APP_DEV_MODE ? $ks : '').']{'.$pw_min.','.$pw_max.'})$/u',
                'regex' => '/^([A-Za-z0-9]|['.$ks.']|\ ){'.$pw_min_standard.','.$pw_max.'}$/u',
            ],
            'mobilenumber' => [
                'prefix' => '+63',
                // regex below
            ],
            'birthdate' => [
                'min'     => 10, 
                'max'     => 26,
                'regex'   => '/^(0[1-9]|[12][0-9]|3[01])\ ([a-zA-Z]{3})\ ([0-9]{4})$/u',  // regex in (d M Y)
                'format_in'    => 'd M Y',
                'format_out'   => 'Y-m-d',
                'format_db'   => DT::getStandardDTFormat(),
                'date_min'     => DT::STR_TryParse('1900-01-01 00:00:00.000000', '', [DT::getServerTZ(), 'UTC']),
                'date_max'     => DT::now_str(),
            ],
            'address' => [
                //'regex' => '/^([a-zA-Z\']+[\ ]*)+$/u',
                'min' => 1,
                'max' => 255,
                'regex' => '/^(?!.*([\'\,\-\.\/])\1)([A-Za-z0-9Ññ]+([\'\-\/][A-Za-z0-9Ññ]|[\,\.]|\.\,)?(\ )?)+$/u',
            ],
            'zipcode' => [
                'min' => 1,
                'max' => 12,
                'regex' => '/^(?!.*([-])\1)([A-Za-z0-9]+([-][A-Za-z0-9])?)*$/u',
            ],
            'relation' => [
                'min' => 1,
                'max' => 50,
                'regex' => "/^(?!.*([',-])\\1)([A-Za-z0-9]+(['-][A-Za-z0-9]|[,.]|\.,)?( )?)+$/u",
            ],
        ];
        $arr1['occupation'] = $arr1['relation'];
        $arr1['coursedegree'] = $arr1['relation'];
        $arr1['company'] = $arr1['address'];

        // converter function in birthdate
        $arr1['birthdate']['converter'] = function(string $str) use($arr1) {
            $str2 = AppFn::STR_regex_eval($arr1['birthdate']['regex'], $str, 
                function(string $pattern, string $subject, mixed $output) use($arr1) {
                    $output = DT::STR_TryParseUTC($subject.' 00:00:00.000000', [$arr1['birthdate']['format_in'].' H:i:s.u', $arr1['birthdate']['format_db']], WebClient::getTimeZone());
                    return $output;
                }
            );
            return $str2;
        };


        // create mobilenumber regex dependent from its prefix above
        $arr1['mobilenumber']['regex'] = '/^('.preg_quote($arr1['mobilenumber']['prefix']).'){1}([0-9]){10}$/u';
        
        // override email max length based from DB
        $FI_email = CLHF::DB_ColInfo('users', 'email');
        $arr1['email']['max'] = ($FI_email[1] < $arr1['email']['max']) ? $FI_email[1] : $arr1['email']['max'];

        // override auth_with max length based from DB
        $FI_authwith = CLHF::DB_ColInfo('users', 'auth_with');
        $arr1['auth_with']['max'] = ($FI_authwith[1] < $arr1['auth_with']['max']) ? $FI_authwith[1] : $arr1['auth_with']['max'];

        //dd($arr1);
        return $arr1;
    }

    public static function getEmailVerification() {
        $FR_App = FieldRules::getGeneral();
        $DATA = [];

        $FR = [];
        $FR['code'] = CLHF::DB_ColInfo('user_emailverify', 'code');
        $code_max = ($FR['code'][1] > 100) ? 100 : $FR['code'][1];

        $DATA['code'] = [
            'min'     => 1, 
            'max'     => $code_max, 
            'regex'   => '/^[A-Za-z0-9]{'.$code_max.'}$/u',
        ];

        return $DATA;
    }
























    public static function getSISPersonal() {
        $GR = SELF::getGeneral();
        $FR2 = SELF::getSISAddrCurr();
        $DBR = [];  // DB Rules

        $CI = [];
        $CI['lname']          = CLHF::DB_ColInfo('ad_lnames', 'lname');
        $CI['fname']          = CLHF::DB_ColInfo('ad_fnames', 'fname');
        $CI['mname']          = CLHF::DB_ColInfo('ad_mnames', 'mname');
        $CI['namex']          = CLHF::DB_ColInfo('pl_namexs', 'namex');
        $CI['birthsex']       = CLHF::DB_ColInfo('pl_birthsexes', 'birthsex');
        $CI['birthdate']      = CLHF::DB_ColInfo('ad_birthdates', 'birthdate');
        $CI['nationality']    = CLHF::DB_ColInfo('pl_nationalities', 'nationality');
        $CI['religion']       = CLHF::DB_ColInfo('pl_religions', 'religion');
        $CI['civilstatus']    = CLHF::DB_ColInfo('pl_civilstatuses', 'civilstatus');
        $CI['mobilenumber']   = CLHF::DB_ColInfo('ad_mobilenumbers', 'mobilenumber');
        $CI['email']          = CLHF::DB_ColInfo('ad_emails', 'email');

        $DBR['lname'] = [
            'min'     => 1, 
            'max'     => $CI['lname'][1], 
            'regex'   => $GR['pname']['regex'],
        ];
        $DBR['fname'] = [
            'min'     => $GR['pname']['min'], 
            'max'     => $CI['fname'][1], 
            'regex'   => $GR['pname']['regex'],
        ];
        $DBR['mname'] = [
            'min'     => 0,  // zero since its optional | $GR['pname']['min'], 
            'max'     => $CI['mname'][1], 
            'regex'   => $GR['pname']['regex'],
        ];
        $DBR['namex'] = [
            'min'     => 0,  // zero since its optional
            'max'     => $CI['namex'][1],
        ];
        $DBR['birthsex'] = [
            'min'     => 1, 
            'max'     => $CI['birthsex'][1],
        ];
        $DBR['birthdate'] = $GR['birthdate'];
        $DBR['birthdate']['max'] = $CI['birthdate'][1];

        $DBR['birthplace_country'] = $FR2['RC_country'];
        $DBR['birthplace_ps']      = $FR2['RC_ps'];
        $DBR['birthplace_cm']      = $FR2['RC_cm'];

        $DBR['maiden_lname']       = $DBR['lname'];
        $DBR['maiden_fname']       = $DBR['fname'];
        $DBR['maiden_mname']       = $DBR['mname'];
        $DBR['maiden_namex']       = $DBR['namex'];

        $DBR['nationality'] = [
            'min'     => 1, 
            'max'     => $CI['nationality'][1],
        ];
        $DBR['religion'] = [
            'min'     => 1, 
            'max'     => $CI['religion'][1],
        ];
        $DBR['civilstatus'] = [
            'min'     => 1, 
            'max'     => $CI['civilstatus'][1],
        ];
        $DBR['email'] = [
            'min'     => $GR['email']['min'], 
            'max'     => $GR['email']['max'], 
            'regex'   => $GR['email']['regex'],
        ];
        $DBR['mobilenumber'] = [
            'min'     => 13, 
            'max'     => $CI['mobilenumber'][1],
            'regex'   => $GR['mobilenumber']['regex'],
        ];
        $DBR['disability'] = [
            'min'     => 5,
            'max'     => 50,
            'regex'   => $GR['address']['regex'],  // since its identical to address
        ];
        $DBR['disabilities'] = [  // array
            'min'     => 1,  // array size min
            'max'     => 10,  // array size max
        ];
        //dd($DBR);
        return $DBR;
    }




















    


    public static function getSISAddrCurr() {
        $FR_App = SELF::getGeneral();
        $DBR = [];  // DB Rules
        $FR = [];

        // current
        $FR['RC_lname']          = CLHF::DB_ColInfo('ad_lnames', 'lname');
        $FR['RC_fname']          = CLHF::DB_ColInfo('ad_fnames', 'fname');
        $FR['RC_mname']          = CLHF::DB_ColInfo('ad_mnames', 'mname');
        $FR['RC_namex']          = CLHF::DB_ColInfo('pl_namexs', 'namex');
        $FR['RC_relation']       = CLHF::DB_ColInfo('ad_relations', 'relation');
        $FR['RC_mobilenumber']   = CLHF::DB_ColInfo('ad_mobilenumbers', 'mobilenumber');
        $FR['RC_email']          = CLHF::DB_ColInfo('ad_emails', 'email');
        $FR['RC_country']        = CLHF::DB_ColInfo('pl_countries', 'country');
        $FR['RC_ps']             = CLHF::DB_ColInfo('ad_place_ps', 'place');
        $FR['RC_cm']             = CLHF::DB_ColInfo('ad_place_cm', 'place');
        $FR['RC_place']          = CLHF::DB_ColInfo('ad_places', 'place');
        $FR['RC_zipcode']        = CLHF::DB_ColInfo('ad_zipcodes', 'zipcode');

        // CURRENT
        $DBR['RC_lname'] = [
            'min'     => 1, 
            'max'     => $FR['RC_lname'][1], 
            'regex'   => $FR_App['pname']['regex'],
        ];
        $DBR['RC_fname'] = [
            'min'     => 1, 
            'max'     => $FR['RC_fname'][1], 
            'regex'   => $FR_App['pname']['regex'],
        ];
        $DBR['RC_mname'] = [
            'min'     => 0, 
            'max'     => $FR['RC_mname'][1], 
            'regex'   => $FR_App['pname']['regex'],
        ];
        $DBR['RC_namex'] = [
            'min'     => 0, 
            'max'     => $FR['RC_namex'][1], 
            'regex'   => '/^(.*?)$/u',
        ];
        $DBR['RC_relation'] = [
            'min'     => 1, 
            'max'     => $FR['RC_relation'][1], 
            'regex'   => $FR_App['relation']['regex'],
        ];
        $DBR['RC_mobilenumber'] = [
            'min'     => 1, 
            'max'     => $FR['RC_mobilenumber'][1], 
            'regex'   => $FR_App['mobilenumber']['regex'],
        ];
        $DBR['RC_email'] = [
            'min'     => 1, 
            'max'     => $FR['RC_email'][1], 
            'regex'   => $FR_App['email']['regex'],
        ];
        $DBR['RC_country'] = [
            'min'     => 1, 
            'max'     => $FR['RC_country'][1], 
            'regex'   => '/^(.*?)$/u',
        ];
        $DBR['RC_ps'] = [
            'min'     => 1, 
            'max'     => $FR['RC_ps'][1], 
            'regex'   => $FR_App['address']['regex'],
        ];
        $DBR['RC_cm'] = [
            'min'     => 1, 
            'max'     => $FR['RC_cm'][1], 
            'regex'   => $FR_App['address']['regex'],
        ];
        $DBR['RC_place'] = [
            'min'     => 1, 
            'max'     => $FR['RC_place'][1], 
            'regex'   => $FR_App['address']['regex'],
        ];
        $DBR['RC_zipcode'] = [
            'min'     => $FR_App['zipcode']['min'],//1, 
            'max'     => $FR_App['zipcode']['max'],//$FR['RC_zipcode'][1], 
            'regex'   => $FR_App['zipcode']['regex'], 
        ];
        
        return $DBR;
    }

    public static function getSISAddrEmgn() {
        $GR = SELF::getGeneral();  // general rule
        $PR = SELF::getSISAddrCurr();  // parent rule
        $FR = [];
        
        // emergency        
        $FR['RE_lname']          = $PR['RC_lname'];
        $FR['RE_fname']          = $PR['RC_fname'];
        $FR['RE_mname']          = $PR['RC_mname'];
        $FR['RE_namex']          = $PR['RC_namex'];
        $FR['RE_relation']       = $PR['RC_relation'];
        $FR['RE_mobilenumber']   = $PR['RC_mobilenumber'];
        $FR['RE_email']          = $PR['RC_email'];
        $FR['RE_country']        = $PR['RC_country'];
        $FR['RE_ps']             = $PR['RC_ps'];
        $FR['RE_cm']             = $PR['RC_cm'];
        $FR['RE_place']          = $PR['RC_place'];
        $FR['RE_zipcode']        = $PR['RC_zipcode'];
        
        $DBR = $FR;
        return $DBR;
    }

    public static function getSISAddrHome() {        
        $GR = SELF::getGeneral();  // general rule
        $PR = SELF::getSISAddrCurr();  // parent rule
        $FR = [];

        // home
        $FR['RH_country']        = $PR['RC_country'];
        $FR['RH_ps']             = $PR['RC_ps'];
        $FR['RH_cm']             = $PR['RC_cm'];
        $FR['RH_place']          = $PR['RC_place'];
        $FR['RH_zipcode']        = $PR['RC_zipcode'];

        $DBR = $FR;
        return $DBR;
    }

    public static function getSISAddress() {        
        $arr1 = SELF::getSISAddrCurr();
        $arr2 = SELF::getSISAddrEmgn();
        $arr3 = SELF::getSISAddrHome();
        $arr = array_merge($arr1, $arr2, $arr3);
        return $arr;
    }




    












    public static function getSISFamilyPare() {        
        $GR = SELF::getGeneral();  // general rule
        $DR = [];  // db rule
        $FR = [];  // field rule

        // FATHER
        $DR['f_lname']          = CLHF::DB_ColInfo('ad_lnames', 'lname');
        $DR['f_fname']          = CLHF::DB_ColInfo('ad_fnames', 'fname');
        $DR['f_mname']          = CLHF::DB_ColInfo('ad_mnames', 'mname');
        $DR['f_namex']          = CLHF::DB_ColInfo('pl_namexs', 'namex');
        $DR['f_birthsex']       = CLHF::DB_ColInfo('pl_birthsexes', 'birthsex');
        //$DR['f_birthdate']      = CLHF::DB_ColInfo('ad_birthdates', 'birthdate');
        $DR['f_occupation']     = CLHF::DB_ColInfo('ad_occupations', 'occupation');
        $DR['f_mobilenumber']   = CLHF::DB_ColInfo('ad_mobilenumbers', 'mobilenumber');

        // MOTHER
        $DR['m_lname']          = $DR['f_lname'];
        $DR['m_fname']          = $DR['f_fname'];
        $DR['m_mname']          = $DR['f_mname'];
        $DR['m_namex']          = $DR['f_namex'];
        $DR['m_birthsex']       = $DR['f_birthsex'];
        //$DR['m_birthdate']      = $DR['f_birthdate'];
        $DR['m_occupation']     = $DR['f_occupation'];
        $DR['m_mobilenumber']   = $DR['f_mobilenumber'];
    
        // FATHER
        $FR['f_lname'] = [
            'min'     => 1, 
            'max'     => $DR['f_lname'][1], 
            'regex'   => $GR['pname']['regex'],
        ];
        $FR['f_fname'] = [
            'min'     => 1, 
            'max'     => $DR['f_fname'][1], 
            'regex'   => $GR['pname']['regex'],
        ];
        $FR['f_mname'] = [
            'min'     => 1, 
            'max'     => $DR['f_mname'][1], 
            'regex'   => $GR['pname']['regex'],
        ];
        $FR['f_namex'] = [
            'min'     => 1, 
            'max'     => $DR['f_namex'][1], 
            //'regex'   => $GR['pname']['regex'],
        ];
        $FR['f_birthsex'] = [
            'min'     => 1, 
            'max'     => $DR['f_birthsex'][1], 
        ];
        /*$FR['f_birthdate'] = [
            'min'          => 10, 
            'max'          => $DR['f_birthdate'][1],
            'format_in'    => 'd M Y',
            'format_out'   => 'Y-m-d',
            'date_min'     => DT::STR_TryParse('1900-01-01 00:00:00.000000', '', [DT::getServerTZ(), 'UTC']),
            'date_max'     => DT::now_str(),
            'converter'    => $GR['birthdate']['converter'],
        ];*/
        $FR['f_occupation'] = [
            'min'     => 1, 
            'max'     => $DR['f_occupation'][1], 
            'regex'   => $GR['occupation']['regex'],
        ];        
        $FR['f_mobilenumber']= [
            'min'     => 13, 
            'max'     => $DR['f_mobilenumber'][1],
            'regex'   => $GR['mobilenumber']['regex'],
        ];

        // MOTHER
        $FR['m_lname']          = $FR['f_lname'];
        $FR['m_fname']          = $FR['f_fname'];
        $FR['m_mname']          = $FR['f_mname'];
        $FR['m_namex']          = $FR['f_namex'];
        $FR['m_birthsex']       = $FR['f_birthsex'];
        //$FR['m_birthdate']      = $FR['f_birthdate'];
        $FR['m_occupation']     = $FR['f_occupation'];
        $FR['m_mobilenumber']   = $FR['f_mobilenumber'];    
        
        return $FR;
    }

    public static function getSISFamilySibl() {
        // prefix of `b_`as blood, `s_` is taken by spouse group
        
        $GR = SELF::getGeneral();  // general rule
        $GR2 = SELF::getSISPersonal();  // general rule personal
        $PR = SELF::getSISFamilyPare();  // parent rule
        $DR = [];  // db rule
        $FR = [];  // field rule

        $DR['b_coursedegree']   = CLHF::DB_ColInfo('ad_coursesdegrees', 'course_degree');
        $DR['b_company']        = CLHF::DB_ColInfo('ad_companies', 'company');

        // sibling
        $FR['b_lname']          = $PR['f_lname'];
        $FR['b_fname']          = $PR['f_fname'];
        $FR['b_mname']          = $PR['f_mname'];
        $FR['b_namex']          = $PR['f_namex'];
        $FR['b_birthsex']       = $PR['f_birthsex'];
        $FR['b_birthdate']      = $GR2['birthdate'];
        $FR['b_coursedegree']   = [
            'min'     => 1, 
            'max'     => $DR['b_coursedegree'][1], 
            'regex'   => $GR['coursedegree']['regex'],
        ];
        $FR['b_company']        = [
            'min'     => 1, 
            'max'     => $DR['b_company'][1], 
            'regex'   => $GR['company']['regex'],
        ];
        $FR['b_occupation']     = $PR['f_occupation'];

        return $FR;
    }

    public static function getSISFamilySpou() {
        $GR = SELF::getGeneral();  // general rule
        $GR2 = SELF::getSISPersonal();  // general rule personal
        $PR = SELF::getSISFamilyPare();  // parent rule
        $DR = [];  // db rule
        $FR = [];  // field rule

        $DR['s_coursedegree']   = CLHF::DB_ColInfo('ad_coursesdegrees', 'course_degree');
        $DR['s_company']        = CLHF::DB_ColInfo('ad_companies', 'company');

        // sibling
        $FR['s_lname']          = $PR['f_lname'];
        $FR['s_fname']          = $PR['f_fname'];
        $FR['s_mname']          = $PR['f_mname'];
        $FR['s_namex']          = $PR['f_namex'];
        $FR['s_birthsex']       = $PR['f_birthsex'];
        $FR['s_birthdate']      = $GR2['birthdate'];
        /*$FR['s_coursedegree']   = [
            'min'     => 1, 
            'max'     => $DR['s_coursedegree'][1], 
            'regex'   => $GR['coursedegree']['regex'],
        ];
        $FR['s_company']   = [
            'min'     => 1, 
            'max'     => $DR['s_company'][1], 
            'regex'   => $GR['company']['regex'],
        ];*/
        $FR['s_occupation']     = $PR['f_occupation'];
        $FR['s_mobilenumber']   = $PR['f_mobilenumber'];   

        return $FR;
    }

    public static function getSISFamily() {        
        $arr1 = SELF::getSISFamilyPare();
        $arr2 = SELF::getSISFamilySibl();
        $arr3 = SELF::getSISFamilySpou();
        $arr = array_merge($arr1, $arr2, $arr3);
        return $arr;
    }















}


