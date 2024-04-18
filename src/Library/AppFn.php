<?php

namespace Rguj\Laracore\Library;

// ----------------------------------------------------------
use App\Providers\RouteServiceProvider;
//use Illuminate\Http\Request;
use Rguj\Laracore\Request\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

use Carbon\Carbon;
use File;
use Exception;
use App\Models\User;

use Rguj\Laracore\Library\CLHF;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\HttpResponse;
use Rguj\Laracore\Library\StorageAccess;
use Rguj\Laracore\Library\WebClient;
// ----------------------------------------------------------

/**
 * Application functions
 *
 * @deprecated 1.0.0
 */
class AppFn {

    /*
        SET-UP GUIDE:

            Run the command in your project:
                composer require jenssegers/agent

            Include these in every controller/middleware as needed:
                use App\Libraries\AppFn;
                use App\Libraries\DT;
                use App\Libraries\WebClient;
                use App\Libraries\CLHF;


        dd(get_defined_vars()['__data'])
    */



    public function __construct() {


    }











    public static function JED($var) {
        // JSON Echo & Die
        //if(is_string($var)) $var = '"'.$var.'"';
        echo json_encode($var);
        die();
    }







    public static function ARRAY_merge_ksort() {
        $args = func_get_args();
        $arr_new = array_merge(...$args);  // only accepts parent integer keys
        ksort($arr_new);
        return $arr_new;
    }


    /*public static function get_calling_class() {

        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        $class = $trace[1]['class'];

        // +1 to i cos we have to account for calling this function
        for ( $i=1; $i<count( $trace ); $i++ ) {
            if ( isset( $trace[$i] ) ) // is it set?
                 if ( $class != $trace[$i]['class'] ) // is it a different class
                     return $trace[$i]['class'];
        }
    }*/






































    public static function GetModuleID(string $mdl_type, string $mdl_name) {
        $mdl_types = array('category', 'menu', 'submenu');
        $mdl_name = trim($mdl_name);
        if(!in_array($mdl_type, $mdl_types) || $mdl_name === '')
            return null;

        $row = DB::select('select * from mdl_'.$mdl_type.' where name=? and is_valid=1 LIMIT 1', [$mdl_name]);
        return count($row)>0 ? $row[0]->id : null;
    }

    public static function GetModuleName(string $mdl_type, int $mdl_id) {
        $mdl_types = array('category', 'menu', 'submenu');
        if(!in_array($mdl_type, $mdl_types) || $mdl_id === null)
            return null;

        $row = DB::select('select * from mdl_'.$mdl_type.' where id=? and is_valid=1 LIMIT 1', [$mdl_id]);
        return count($row)>0 ? $row[0]->name : null;
    }

    public static function getUserTypeName(int $user_type_id) {
        // this does not check `is_valid`

        if($user_type_id === -1) // Hard coded
            return 'Super Admin';

        $user_type = '';
        $row = DB::select('select * from user_roles where id=? limit 1', [$user_type_id]);
        $user_type = ($row!=null) ? $row[0]->role : '';
        return $user_type;
    }

    public static function getUserTypes(int $user_id) {
        $user_type = array();
        if($user_id > 0) {
            $rows1 = DB::select('select * from user_type where user_id=?', [$user_id]);
            //dd($rows1);
            foreach($rows1 as $row1) {
                $user_type[] = $row1->user_role_id;
            }
        }
        return $user_type; // returns array
    }

    public static function getUserTypeIDs(int $user_id) {
        $db1 = DB::table('user_type')->where(['user_id'=>$user_id])->orderBy('user_role_id', 'ASC');
        $user_type_ids_ = $db1->get();
        $user_type_ids = [];
        foreach($user_type_ids_ as $key=>$val) {
            $user_type_ids[] = $val->user_role_id;
        }
        return $user_type_ids;
    }

    public static function IsModuleAccessAllowed(int $user_id, string $uri) {
        // this function does not include checking Auth::user
        // URI must not be empty

        $IsModuleAccessAllowed = false;
        $uri = str_replace(' ', '', $uri);

        if($uri === '')
            return false;

        $user_type_ids = SELF::getUserTypes($user_id);
        //dd($user_type_ids);

        foreach($user_type_ids as $user_type_id) {
            $rows1 = DB::select('select * from user_acl where user_role_id=? and uri=?', [$user_type_id, $uri]); //dd($rows1);

            if(count($rows1) > 0)
                return true;
        }
        return $IsModuleAccessAllowed;
    }
















    public function JSON_FetchFile(string $filepath) {
        // public_path()."\assets\json\\filename.json"
        $json = [];
        $json_str = file_get_contents($filepath);
        $_json = json_decode($json_str, true);
        foreach($_json as $line) {
            $json[] = $line;
        }
        return $json;
    }














    public static function OBJECT_toArray($obj) {
        /*
            converts PHP array or Object Instance to a pure array
            doesn't yet supports multi depth objects
        */

        $output = [];
        //if(!(is_object($obj) || is_array($obj)))
        //    throw new Exception('`$obj` must be an object or array');
        /*$classes = [
            'Illuminate\Support\ViewErrorBag',
            'Illuminate\Support\Collection',
        ];*/

        $output = (array)AppFn::OBJECT_reflect($obj, true);
        return $output;
    }

    /*public static function OBJECT_toArrayColumn($obj, $col) {
        $arr = AppFn::OBJECT_toArray($obj);
        return array_column($arr, $col);
    }*/

    public static function OBJECT_reflect($obj, bool $to_array=false) {
        // converts protected/unprotected objects to object/array

        $recode = function($arr) {
            return json_decode(json_encode($arr), true);
        };

        $reflector = function($obj1, bool $to_array=false) use($recode) {
            $retval = [];
            if(!(is_object($obj1) || is_array($obj1)))
                throw new Exception('`$obj` must be an object or array');
            if(is_array($obj1)) {
                //$retval = $recode($obj1);
                $retval = AppFn::ARRAY_recode($obj1);
                goto point1;
            }
            $reflection = new \ReflectionClass($obj1);
            $props = $reflection->getProperties();
            $obt = (object)[];
            foreach($props as $key2=>$val2) {
                $prop_name = $val2->name;
                $prop = $reflection->getProperty($prop_name);
                $prop->setAccessible(true);
                $obt->$prop_name = $prop->getValue($obj1);
            }
            $retval = !empty($obt) ? ($to_array ? AppFn::ARRAY_recode($obt) : $obt) : $retval;
            point1:
            return $retval;
        };

        return $reflector($obj, $to_array);
    }

    public static function is_closure($obj) {
        $bool = false;
        try {
            $reflection = new \ReflectionFunction($obj);
            $bool = (bool)$reflection->isClosure();
        } catch(\Throwable $th) {}
        return $bool;
    }











    public static function URL_parse(string $fullUrl) {
        $arr1 = $arr2 = parse_url($fullUrl);

        $arr2['path_'] = [];
        foreach(explode('/', $arr1['path']) as $key=>$val) {
            if(AppFn::STR_IsBlankSpace($val) !== true) {
                $arr2['path_'][] = $val;
            }
        }

        parse_str($arr1['query'], $arr2['query_']);

        $query_ = [];
        foreach($arr2['query_'] as $key=>$val) {
            $query_[urldecode($key)] = urldecode($val);
        }
        $arr2['query_'] = $query_;

        return $arr2;
    }




















    public static function USER_BasicInfo(int $user_id) {

        $db1 = DB::table('users')->where(['id'=>$user_id]);
        $db2 = DB::table('user_profile')->where(['user_id'=>$user_id]);
        $is_found = ($db1->exists() && $db2->exists()) ? true : false;

        $data_user = $db1->first() ?? [];
        $data_userprofile = $db2->first() ?? [];

        $email = $data_user->email ?? '';
        $lname = trim($data_userprofile->lname ?? '');
        $fname = trim($data_userprofile->fname ?? '');
        $mname = trim($data_userprofile->mname ?? '');

        // CONVERT CASE
        $lname = SELF::STR_UTF8CC($lname, 'title_simple');
        $fname = SELF::STR_UTF8CC($fname, 'title_simple');
        $mname = SELF::STR_UTF8CC($mname, 'title_simple');

        // combined words
        $flname = trim($fname.' '.$lname);
        $lfname = trim($lname.' '.$fname);

        // USER_TYPE IDS
        $user_type_ids = SELF::getUserTypeIDs($user_id);
        $str_user_types = ''; $c=-1;
        $arr_user_types = [];
        foreach($user_type_ids as $key=>$val) {
            $c++;
            $user_type = static::getUserTypeName($val);
            $arr_user_types[] = $user_type;
            $str_user_types .= ($c > 0) ? '|'.ucfirst($user_type) : ucfirst($user_type);
        }

        // NAMING CONVENTIONS/FORMATS
        $name_formats = [

        ];

        // FORMING DATA
        $user_data = [
            'email' => $email,
            'lname' => $lname,
            'fname' => $fname,
            'mname' => $mname,

            //'name_formats' => $name_formats,
            'flname' => $flname,
            'lfname' => $lfname,
            'is_found' => $is_found,
            'user_types' => $arr_user_types,
        ];

        return $user_data;
    }

    public static function USER_getNavigation(int $user_id) {
        $data = [];

        return $data;
    }

    public static function USER_getNotifications(int $user_id) {
        $data = [];

        return $data;
    }




















    # ----------------------------------------------------------
    # \ STRING
    # ----------------------------------------------------------

    public static function STR_GetKeyboardSymbols(bool $escape=false) {
        $symbols = "!@#$%^&*()-_=+[{]};:'".'"'."\|,<.>/?`~";  // double quotes intentionally isolated
        $output = '';
        if(!$escape) {
            $output = $symbols;
            goto point1;
        }
        $pcs = str_split($symbols);
        foreach($pcs as $key=>$val) {
            $output .= $pcs[22].$val;
        }
        point1:
        return $output;
    }

    public static function STR_MD5Equals(string $val, string $var) {
        // requires non empty values to check
        return (!empty(trim($var)) && !empty(trim($val)) && MD5($val) === $var);
    }

    public static function STR_JSIfEmptyNullElseValue($data, bool $strict=false) {
        $null = 'null';
        if(!in_array(gettype($data), ['boolean', 'string']))
            return $null;
        if($data === null)
            return $null;
        $data2 = $strict ? trim($data) : $data;
        return (!empty($data2) ? "'".$data."'" : $null);
    }

    public static function STR_IsNumber(string $num_str) {
        $regex = '/^([\+\-])?([0-9])+(\.[0-9]*)?$/u';
        return AppFn::STR_preg_match($regex, $num_str);
    }

    public static function STR_IsPositiveNumericInteger(string $num_str) {
        $regex = '/^([\+])?([0-9])+$/u';
        return AppFn::STR_preg_match($regex, $num_str);
    }

    public static function STR_TransToDLFileName(string $str, int $char_limit=15, string $ellipsis='') {
        // Translate to Download File Name
        // $str1 = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities($apt['lfname']));
        // transliterate accents/diacritics chars (e.g. "GracišceâêîôûñÑÀÈÌÒÙ")
        $output = Str::of($str)->trim()->ascii()->lower()->limit($char_limit, $ellipsis)->slug('-');
        return $output;
    }

    public static function STR_Sanitize(string $str, bool $one_space=true, bool $with_trim=true) {
        // Trim unicode/UTF-8 whitespace in PHP
        // 1) Replaces any weird whitespace characters, control characters INTO space (ascii 32)
        // 2) Replace chained spaces into one space
        // 3) Trim leading and trailing spaces

        $charcode_preserve = [9, 32];  // tab, space
        $str_split = mb_str_split($str);
        $new_str1 = '';
        foreach($str_split as $key=>$val) {
            $ch_ord = ord($val);
            if(in_array($ch_ord, $charcode_preserve)) {
                $new_str1 .= $val;
            } else if(AppFn::STR_preg_match('/^[\pZ\pC]+|[\pZ\pC]+$/u', $val)) {
                $new_str1 .= ' ';
            } else {
                $new_str1 .= $val;
            }
        }
        //$new_str1 = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', ' ', $str);
        $new_str2 = $one_space ? (string)(preg_replace('/\s+/u', ' ', $new_str1)) : $new_str1;
        $new_str3 = $with_trim ? trim($new_str2) : $new_str2;
        return $new_str3;
    }

    public static function STR_IsBlankSpace(string $str) {
        // catches weird whitespace characters(alt 255, etc) character
        return (bool)empty(AppFn::STR_Sanitize($str));
    }



    public static function STR_NotEmptyEval(string $str, $val_true, $val_false) {
        return !SELF::STR_IsBlankSpace($str) ? $val_true : $val_false;
    }

    public static function STR_NotEmptyEvalSelf(string $str, $val_false) {
        return SELF::STR_NotEmptyEval($str, $str, $val_false);
    }


    /**
     * Checks string if filled and can be manipulated
     *
     * @param string $str
     * @param \Closure $func function($not_empty, $str) { }
     * @return void
     */
    public static function STR_NotEmptyEval2(string $str, \Closure $func) {
        $not_empty = (AppFn::STR_IsBlankSpace($str) !== true);
        return $func->__invoke($not_empty, $str);
    }



    public static function STR_GenerateRandomAlphaNum(int $length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function STR_MBCC(string $string, int $mode, string $encoding) {
        // string multibyte convert case
        $output = mb_convert_case($string, $mode, $encoding);
        return $output;
    }

    public static function STR_UTF8CC(string $string, string $str_mode, array $preserve_word=[], array $symbols=['-']) {
        // string UTF-8 convert case
        /*
            MODES: upper, lower, title, fold, upper_simple, lower_simple, title_simple, fold_simple
            EXTRA MODES: title2

            Examples:
                STR_UTF8CC('háčeKŭ œæZZZåðñ', 'upper') => HÁČEKŬ ŒÆZZZÅÐÑ
                STR_UTF8CC('háčeKŭ œæZZZåðñ', 'lower') => háčekŭ œæzzzåðñ
                STR_UTF8CC('háčeKŭ œæZZZåðñ', 'title') => Háčekŭ Œæzzzåðñ
                STR_UTF8CC('háčeKŭ œæZZZåðñßßß', 'fold') => háčekŭ œæzzzåðñssssss

        */
        $encoding = 'UTF-8';
        $str_mode = AppFn::STR_Sanitize(strtolower($str_mode));
        $modes = [
            'upper'          => MB_CASE_UPPER,          // 0
            'lower'          => MB_CASE_LOWER,          // 1
            'title'          => MB_CASE_TITLE,          // 2
            'fold'           => MB_CASE_FOLD,           // 3
            'upper_simple'   => MB_CASE_UPPER_SIMPLE,   // 4
            'lower_simple'   => MB_CASE_LOWER_SIMPLE,   // 5
            'title_simple'   => MB_CASE_TITLE_SIMPLE,   // 6
            'fold_simple'    => MB_CASE_FOLD_SIMPLE,    // 7
        ];

        // check structure of $preserve_word
        $arr_type1 = AppFn::ARRAY_AnalyzeStructure($preserve_word);
        if($arr_type1[0] !== 'empty') {
            $bool1 = ($arr_type1[0] === 'associative' && $arr_type1[1] === 'sequential');
            if($bool1 === true) {
                foreach($preserve_word as $key=>$val) {
                    if(!array_key_exists($key, $modes)) {
                        throw new Exception('Unknown key `'.$key.'`');
                    }
                }
            } else
                throw new Exception('Array structure must be `associative => sequential`');
        }

        if(!in_array(AppFn::ARRAY_AnalyzeStructure($symbols)[0], ['empty', 'sequential']))
            throw new Exception('$symbols must be sequential array');

        // title v2 (doesn't convert to upper case the chars after dash)
        if($str_mode === 'title2') {
            //$output = '';
            $batch1 = AppFn::STR_UTF8CC($string, 'title');

            foreach($preserve_word as $k=>$v) {
                $symbols = ['-'];  // add dash to change case the character after dash
                $count_group1 = ['single' => [], 'multis'=>[]];
                $count_group2 = ['single' => [], 'multis'=>[]];

                // separate single to multiple chars
                foreach($v as $key=>$val) {
                    $val2 = AppFn::STR_Sanitize($val);
                    if(strlen($val)>0) {
                        if(strlen($val)>1)
                            $count_group2['multis'][] = AppFn::STR_UTF8CC($val, 'lower');
                        else
                            $count_group2['single'][] = AppFn::STR_UTF8CC($val, 'lower');
                    }
                }

                // PROCESS MULTIS
                $str3 = '';
                $pcs1 = explode(' ', $batch1);
                foreach($pcs1 as $key=>$val) {
                    $val2 = AppFn::STR_UTF8CC($val, 'lower');
                    $str3 .= ($key>0 ? ' ' : '').(in_array($val2, $count_group2['multis']) ? AppFn::STR_UTF8CC($val, $k) :  $val);
                }

                // PROCESS SINGLES
                $str4 = '';
                $pcs1 = str_split($str3);
                for($x=0; $x<count($pcs1); $x++) {
                    $ch = $pcs1[$x];
                    $ch2 = AppFn::STR_UTF8CC($ch, 'lower');
                    $str4 .= $ch;
                    if(in_array($ch2, $count_group2['single'], true)) {
                        $x1 = $x+1;
                        $ch_next = array_key_exists($x1, $pcs1) ? AppFn::STR_UTF8CC($pcs1[$x1], $k) : '';
                        $str4 .= $ch_next;
                        $x = $x1;
                    }
                }

                $batch1 = $str4;
            }

            // PROCESS SYMBOLS
            $str5 = '';
            $pcs1 = str_split($batch1);
            for($x=0; $x<count($pcs1); $x++) {
                $ch = $pcs1[$x];
                $ch2 = AppFn::STR_UTF8CC($ch, 'lower');
                $str5 .= $ch;
                if(in_array($ch2, $symbols)) {
                    $x1 = $x+1;
                    $ch_next = array_key_exists($x1, $pcs1) ? AppFn::STR_UTF8CC($pcs1[$x1], ($str_mode === 'upper' ? 'upper' : 'lower')) : '';
                    $str5 .= $ch_next;
                    $x = $x1;
                }
            }

            $batch1 = $str5;

            return $batch1;
        }

        $mode = array_key_exists($str_mode, $modes) ? $modes[$str_mode] : null;
        $output = SELF::STR_MBCC($string, $mode, $encoding);
        return $output;
    }

    /*public static function STR_startswith(string $string, string $starts_with) {
        return strpos($string, $starts_with) !== false;
    }*/

    protected static function STR_FormatUserName_Parser(string $current_format, array $data, array $parser_rules, bool $clean_empty=false) {

        $required_keys  = $parser_rules[0];
        $p1_tokens      = $parser_rules[1];
        $p2_tokens      = $parser_rules[2];

        $conf = [
            $required_keys[0] => [$p1_tokens[0], $p1_tokens[4]],  // last
            $required_keys[1] => [$p1_tokens[1], $p1_tokens[5]],  // first
            $required_keys[2] => [$p1_tokens[2], $p1_tokens[6]],  // middle
            $required_keys[3] => [$p1_tokens[3], $p1_tokens[7]],  // extension
        ];

        // full or initial
        $p1 = $current_format[0];
        $p2 = $current_format[1];
        $output1 = '';
        foreach($conf as $key1=>$val1) {
            foreach($val1 as $key2=>$val2) {
                if($p1 === $val2) {
                    $p1_ord = ord($p1);
                    $is_capital = ($p1_ord >= 65 && $p1_ord <= 90);  // 65-90
                    $name_str1 = is_array($data[$key1]) ? $data[$key1][1] : $data[$key1];
                    $name_str2 = AppFn::STR_Sanitize($name_str1);
                    $output1 = $is_capital ? $name_str2 : ($name_str2[0] ?? '');  // fail-safe
                }
            }
        }

        // overriding letter case
        $case_folding = [
            'upper' => [$p2_tokens[0], $p2_tokens[1]],
            'lower' => [$p2_tokens[2], $p2_tokens[3]],
            'title' => [$p2_tokens[4], $p2_tokens[5]],
            ''      => [$p2_tokens[6], $p2_tokens[7]],
        ];
        $output2 = '';
        foreach($case_folding as $key1=>$val1) {
            foreach($val1 as $key2=>$val2) {
                if($p2 === $val2) {
                    $output2 = $key1 !== '' ? AppFn::STR_UTF8CC($output1, $key1) : $output1;
                }
            }
        }

        return $output2;
    }

    public static function STR_FormatUserName(string $format, array $data, bool $clean_empty=false) {

        /*
            Formats (required) (case-sensitive):
                L or l => Last Name (full or initial)
                F or f => First Name (full or initial)
                M or m => Middle Name (full or initial)
                E or e => Name Extension (full or initial)

            Format Suffixes (required) (case-insensitive):
                U or u => Upper case
                S or s => Lower case (since L is already taken)
                T or t => Title case
                N or n => Original case

            Data Array Keys (required):
                last, first, middle, extension

            Example:
                fname = 'john'
                format = 'FT'
                output = "John"
        */

        $p1_tokens = ['l', 'f', 'm', 'e', 'L', 'F', 'M', 'E'];
        $p2_tokens = ['u', 'U', 's', 'S', 't', 'T', 'n', 'N'];
        $required_keys = ['last', 'first', 'middle', 'extension'];
        $parser_rules = [$required_keys, $p1_tokens, $p2_tokens];

        // checkpoint for required array keys
        $missing_key = '';
        foreach($required_keys as $key=>$val) {
            if(!array_key_exists($val, $data)) {
                $missing_key = $val;
                break;
            }
        }
        if(!empty($missing_key))
            throw new exception('Missing array key `'.$missing_key.'`');

        // LEXER
        $matches = 0;
        $format_snz = SELF::STR_Sanitize($format, false, false);
        $format_new = '';
        $format_split = mb_str_split($format_snz);
        $c_format = count($format_split);
        $x_format = -1;
        $skip = 0;
        $rpt = [];
        foreach($format_split as $key=>$ch) {
            $x_format++;
            if($skip > 0) {
                $skip--;
                continue;
            }
            if($ch === ' ') {
                $format_new .= $ch;
                continue;
            }
            $has_next = $key+1 < $c_format;
            $ch_next = $format_split[$key+1] ?? '';
            $current_format = $ch.$ch_next;
            if($has_next === true && in_array($ch, $p1_tokens, true) && in_array($ch_next, $p2_tokens, true)) {
                $skip += strlen($current_format)-1;
                $matches++;
                $parsed = SELF::STR_FormatUserName_Parser($current_format, $data, $parser_rules);  // parser
                $format_new .= $parsed;
                for($x=$key+$skip+1; $x<$c_format; $x++) {  // looking ahead
                    if(in_array($format_split[$x], $p1_tokens)) {
                        break;
                    } else {
                        $skip++;
                        if($format_split[$x] === ' ') {
                            $format_new .= $format_split[$x];
                            continue;
                        } else {
                            $bool1 = ($clean_empty !== true || ($clean_empty === true && AppFn::STR_IsBlankSpace($parsed) !== true));
                            if($bool1 === true) {
                                $format_new .= $format_split[$x];
                                continue;
                            }
                        }
                    }
                }
            } else {
                $skip += strlen($ch)-1;
                if($clean_empty !== true) {
                    $format_new .= $ch;
                }
            }
        }
        $format_new2 = AppFn::STR_Sanitize($format_new);
        $format_new3 = ($clean_empty === true && $matches<=0) ? '' : $format_new2;
        return $format_new3;
    }

    public static function STR_MakePassword(string $str1, string $str2) {
        // str1 => unique ID
        // str2 => microseconds

        $alphas = 'BCDFGHJKLMPQRSTVWXYZ';
        $symbols = '!@#$%^&*()';
        $alphas_split = str_split($alphas, 2);  // group by two
        $symbols_split = str_split($symbols);
        $min_length = 5;
        $length = 10;

        if(strlen($str1) < $min_length)
            throw new exception('str1 min length must be >= '.$min_length);
        if(strlen($str2) < $min_length)
            throw new exception('str2 min length must be >= '.$min_length);

        //$str1_last = (strlen($str1) >= $length) ? substr($str1, -$length) : '4162578093';  // 4162578093
        $str1_last = (strlen($str1) < $length ? ($str1.substr($str1, 0, ($length-strlen($str1)))) : substr($str1, 0, $length));
        $str1_last_split = str_split($str1_last);

        $str2_rpt = (strlen($str2) < $length ? ($str2.substr($str2, 0, ($length-strlen($str2)))) : substr($str2, 0, $length));
        $str2_rpt_split = str_split($str2_rpt);
        $str2_f3 = substr($str2, 0, 3);

        if(strlen($str1_last) < $length)
            throw new exception('str1 length must be >= '.$length);
        if(strlen($str2_rpt) < $length)
            throw new exception('str2 length must be >= '.$length);

        $res = '';
        for($x=0; $x<10; $x++) {
            $arr_pair = $alphas_split[$str1_last_split[$x]];
            $is_capital = ($str1_last_split[$x] >= 5);
            $is_even = ($str2_rpt_split[$x] % 2 === 0);
            $char1 = $is_even ? $arr_pair[0] : $arr_pair[1];
            $char2 = $is_capital ? strtoupper($char1) : strtolower($char1);
            $res .= $char2;
        }

        $symbol = $symbols_split[end($str1_last_split)];
        $res .= strrev($str2_f3).$symbol.$symbol;

        return $res;
    }

    public static function STR_preg_match(string $pattern, string $subject) {//dump($pattern);
        $bool1 = preg_match($pattern, $subject) === 1;
        return $bool1;
    }

    /**
     * Evaluate pattern to subject and if true, manipulate subject value in Closure.
     *
     * @param string $pattern Regular expression
     * @param string $subject
     * @param \Closure $true_func Params (string $pattern, string $subject, mixed $output)
     * @return null|mixed
     */
    public static function STR_regex_eval(string $pattern, string $subject, $true_func=null) {
        $output = null;
        if(AppFn::STR_preg_match($pattern, $subject)) {
            if(AppFn::is_closure($true_func) !== true) {
                $output = !is_null($true_func) ? $true_func : $subject;
            } else {
                $output = $true_func->__invoke($pattern, $subject, null);
            }
        }
        point1:
        return $output;
    }

    public static function STR_copyrightYear() {
        $app_tz = config('app.timezone');
		$app_tz = DT::isTZString($app_tz) ? $app_tz : DT::getStandardTZ();

        $app_created_at = config('env.APP_CREATED_AT');  // assumes it's already UTC
		$created_at = DT::isDTString(DT::getStandardDTFormat(), $app_created_at) ? $app_created_at : DT::now_str('UTC');

		$dt_created = DT::createDateTimeUTC($created_at);
		$dt_now = DT::now($app_tz);

		$cr_year = ($dt_now->startOfYear() > $dt_created->startOfYear()) ? $dt_created->format('Y').'-'.$dt_now->format('Y') : $dt_created->format('Y');

		return $cr_year;
	}



    # ----------------------------------------------------------
    # / STRING
    # ----------------------------------------------------------


















    # ----------------------------------------------------------
    # \ ARRAY
    # ----------------------------------------------------------

    public static function ARRAY_KeyStartsWith(string $needle, array $arr) {
        foreach($arr as $key=>$val) {
            if(is_string($key)) {
                $match1 = AppFn::STR_preg_match('/^(('.preg_quote($needle).')\.[0-9]+)$/u', $key);
                if($match1 > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function ARRAY_GetType(array $arr) {
        // supports only one dimensional array
        if(AppFn::ARRAY_depth($arr) !== 1)
            throw new exception('Must be a 1 dimensional array');
        $struc = AppFn::ARRAY_AnalyzeStructure($arr);
        return $struc[0];
    }

    public static function ARRAY_IsTypeSequential(array $arr) {
        return AppFn::ARRAY_GetType($arr) === 'sequential';
    }

    public static function ARRAY_IsTypeAssociative($arr) {
        return AppFn::ARRAY_GetType($arr) === 'associative';
    }

    public static function ARRAY_key_filled($keys, array $arr) {
        // accepts single key or multiple keys (string | array)
        // combination of array_key_exists() and !empty()
        if(is_string($keys))
            return (array_key_exists($keys, $arr) && !empty($arr[$keys]));
        else if(is_array($keys)) {
            foreach($keys as $key=>$val) {
                if(!(array_key_exists($val, $arr) && !empty($arr[$val])))  // fault finding
                    return false;
            }
            return true;
        } else throw new exception('Invalid type of $key');
    }

    public static function ARRAY_depth(array $array) {
        $max_indentation = 1;
        $array_str = print_r($array, true);
        $lines = explode("\n", $array_str);
        foreach ($lines as $line) {
            $indentation = (strlen($line) - strlen(ltrim($line))) / 4;
            if ($indentation > $max_indentation) {
                $max_indentation = $indentation;
            }
        }
        return (int)(ceil(($max_indentation - 1) / 2) + 1);
    }

    public static function ARRAY_recode($var) {
        return json_decode(json_encode($var), true);
    }

    public static function ARRAY_AnalyzeStructure(array $arr) {
        // parse $arr first using AppFn::OBJECT_toArray
        // supports up to 2 dimension only
        // auto converts null => '', false => 0, true => 1, decimals => integer
        // associative can be int or string that doesn't match the counter

        $dimensions = ['empty', 'sequential', 'associative', 'mixed', 'irregular'];
        $struc = [$dimensions[0], $dimensions[0]];

        if(!is_array($arr))
            throw new exception('$arr must be array');
        if(empty($arr))
            goto point1;

        $identify_structure = function(array $counter, int $total_count) use($dimensions) {
            $structure = $dimensions[0];
            if($counter[0] > 0 && $counter[1] > 0) {  // associative
                $counter[1] = $counter[0] + $counter[1];
                $counter[0] = 0;  //reset sequential count
            }
            if($total_count === $counter[0])
                $structure = $dimensions[1];
            else if($total_count === $counter[1])
                $structure = $dimensions[2];
            else if($total_count === $counter[2])
                $structure = $dimensions[3];
            else if($total_count === $counter[3])
                $structure = $dimensions[4];
            return $structure;
        };

        $analyzer = function($arr2) use($identify_structure) {
            $c = [0, 0, 0, 0, 0];  // [seq, assoc, mixed, irreg, nest]
            $x = -1;
            foreach($arr2 as $key=>$val) {
                $x++;
                if(in_array(gettype($key), ['integer', 'string'])) {
                    if(is_int($key)) {
                        if($key === $x)  $c[0]++;
                        else  $c[1]++;
                    } else if(is_string($key)) {
                        $c[1]++;
                    }
                } else {
                    $c[3]++;
                }
                if(in_array(gettype($val), ['array', 'object']))
                    $c[4]++;
            }
            return $identify_structure($c, $x + 1);
        };

        // split two dimensions
        $dim1 = [];  // dimension 1
        $dim2 = [];  // dimension 2
        $c_dim2_1 = 0;  // group count
        $c_dim2_2 = 0;  // individual count
        foreach($arr as $key=>$val) {
            $dim1[$key] = is_array($val) ? '' : $val;
            if(is_array($val)) {
                $c_dim2_1++;
                $arr0 = [];
                foreach($val as $key2=>$val2) {
                    $c_dim2_2++;
                    if(is_array($val2))
                        throw new exception('Could not handle beyond 2 dimensions.');
                    else
                        $arr0[$key2] = $val2;
                }
                $dim2[] = $arr0;
            }
        }
        $dim1_struc = $analyzer($dim1);  // dim 1

        // ANALYZE DIMENSION 2
        $c = [0, 0, 0, 0];  // [seq, assoc, mixed, irreg]
        $x = -1;
        if(!empty($dim2)) {
            foreach($dim2 as $key=>$val) {
                $x++;// = $x + count($val);
                $a = $analyzer($val);
                $i = (array_keys($dimensions, $a, true)[0] ?? 0) - 1;
                $v = $c[$i] ?? null;
                if($i < 0 || is_null($v))
                    throw new exception('Array value not found.');
                $c[$i]++;
            }
        }
        $dim2_struc = !empty($dim2) ? $identify_structure($c, $x+1) : $struc[1];  // dim 2

        // forming data
        $struc[0] = $dim1_struc;
        $struc[1] = $dim2_struc;
        point1:
        return $struc;
    }

    # ----------------------------------------------------------
    # / ARRAY
    # ----------------------------------------------------------







    # ----------------------------------------------------------
    # \ NUMBER
    # ----------------------------------------------------------


    public static function NUM_SafeDivide($num1, $num2) {
        return ($num1 === 0 || $num2 === 0) ? 0 : $num1/$num2;
    }


    # ----------------------------------------------------------
    # / NUMBER
    # ----------------------------------------------------------


















    /*public static function GET_BS_Progress_Class_Color($progress) {
        $bs_class = '';
        if(gettype($progress) != 'double') return $bs_class;
        if     ($progress < 0.50) $bs_class = 'bg-danger';
        elseif ($progress < 0.75) $bs_class = 'bg-warning';
        elseif ($progress < 1.00) $bs_class = 'bg-info';
        else                      $bs_class = 'bg-success';
        return $bs_class;
    }*/

    public static function STR_GenerateBreadcrumbs(array $data, bool $with_home=true, bool $home_clickable=true) {
        // [[label, link]+]

        $html = '';
        $html .= '<div class="row noselect" style=" margin-top: 10px;"> <div class="col-sm-12"><div class="card bg-dark"><div class="card-body" style="padding: 10px 20px 10px 20px;"><nav aria-label="breadcrumb" style=""><ol class="breadcrumb" style="padding:0px;">';
        $home_data = ['🏠 Dashboard', route('dashboard'), $home_clickable];
        $count_menu = count($data);
        $c = 0;

        if($with_home) {
            array_unshift($data, $home_data);
            $count_menu++;
        }

        foreach($data as $key=>$val) {
            $c++;
            $bool1 = (
                isset($val[0]) && isset($val[1])
                && gettype($val[0]) === 'string'
                && gettype($val[1]) === 'string'
                && (!isset($val[2]) || gettype($val[2]) === 'boolean')
            ); if(!$bool1) continue;

            $isset_3rdparam = isset($val[2]);
            $is_clickable = $isset_3rdparam ? $val[2] : ($c === $count_menu ? false : true);

            $color = ($c === $count_menu) ? '#ffffff' : '#cccccc';
            $active = ($c === $count_menu) ? 'active' : '';
            $aria_current = ($c === $count_menu) ? 'aria-current="page"' : '';

            $content = ($is_clickable) ? '<a href="'.$val[1].'" style="color:'.$color.'">'.$val[0].'</a>' : '<span style="color:'.$color.'; cursor:default;">'.$val[0].'</span>';
            $html .= '<li class="breadcrumb-item '.$active.'" '.$aria_current.'>'.$content.'</li>';
        }
        $html .= '</ol></nav></div></div></div></div>';

        return $html;
    }






































    /*public static function customSelectOption(string $val, string $text, string $old_val="[];'\/.") {
        $attrib_selected = ($val === $old_val) ? 'selected' : '';
        $html = '<option value="'.$val.'" '.$attrib_selected.'>'.$text.'</option>';
        return $html;
    }*/



























    public static function METRONIC_GetVSKeyword(string $validation_state, string $class_success, string $class_error, string $class_neutral) {
        // Get Validation State Keyword => (success, error, neutral[''])
        $arr = ['success', 'error'];
        $keyword = '';
        switch($validation_state) {
            case $arr[0]:
                $keyword = $class_success;
            break;
            case $arr[1]:
                $keyword = $class_error;
            break;
            default:
                $keyword = $class_neutral;
            break;
        }
        return $keyword;
    }

    public static function HTML_ImplodeOtherAttr(array $attrs) {
        // implode html element attributes with one space as separator
        $output = '';
        foreach($attrs as $key=>$val) {
            $output .= ($key > 0 ? ' ' : '').$val;
        }
        return $output;
    }

    public static function HTML_BuildELMValues(array $lconf, $UID) {
        // UID = User Interaction Data

        $errors = $UID['errors'];
        $preloads = $UID['preloads'];
        $fieldrules = $UID['fieldrules'];
        $defaults = $UID['defaults'];

        $_ELM = [];  // HTML ELEMENT SETTINGS
        try {
            $arr = ['name', 'label', 'description', 'placeholder', 'hinter_ipt', 'hinter_lbl', 'is_required', 'is_autofocus', 'is_autofocuserr'];
            $x=-1;
            foreach($arr as $key=>$val) {
                $x++;
                if(!isset($lconf[$val]))
                    throw new exception('Key `'.$val.'` must be declared.');
                if($x>=0 && $x<=5) {
                    if(!is_string($lconf[$val]))
                        throw new exception('Key `'.$val.'` must be string.');
                }
                else if($x>=6 && $x<=8) {
                    if(!is_bool($lconf[$val]))
                        throw new exception('Key `'.$val.'` must be boolean.');
                }
            }
            if(empty($lconf['name']))
                throw new exception('Attribute `'.$val.'` must not be empty.');

        } catch(\Exception $ex) {
            dd($ex->getMessage());
            goto point2;
        }


        $attr_name = $lconf['name'];
        $is_required = $lconf['is_required'];


        $has_error = array_key_exists($attr_name, $errors);
        $feedback_allowed = $lconf['feedback_allowed'] ?? [];
        $bool_feedbacksuccess = in_array('success', $feedback_allowed);
        $bool_feedbackerror   = in_array('error', $feedback_allowed);

        // INTELLIGENT VALUE PICKER
        // if the value is NULL, please check form_defaults AND the old_value
        $value_now = old($attr_name, $defaults[$attr_name] ?? null);
        $_ELM['valu_now'] = $value_now;
        //dump(old($attr_name));
        //dump($defaults[$attr_name]);

        // FORM Field Label = default or error text (AUTO ADJUST LOGIC)
        // Only supports one dimension array of error
        $attr_descDefault = $lconf['description'];
        $attr_descError = array_key_exists($attr_name, $errors) ? ($errors[$attr_name][0] ?? 'ERROR') : '';
        $attr_desc = $has_error ? $attr_descError : $attr_descDefault;  // description label
        $_ELM['valu_description'] = $attr_desc;

        // VSK Class for div.form-group
        $vsk = old(AppFn::CONFIG_env('APP_VSK_NAME', '', 'string').'.'.$attr_name, '');
        $fg_state_div = 'fg-normal';
        if($vsk === 'error') {
            $fg_state_div = ($bool_feedbackerror? 'fg-error' : 'fg-normal');
        } else if($vsk === 'success') {
            $fg_state_div = ($bool_feedbacksuccess ? 'fg-success' : 'fg-normal');
        }
        $_ELM['clss_fg'] = $fg_state_div;

        // INTELLIGENT AUTOFOCUS
        $is_autofocus = $lconf['is_autofocus'];
        $is_autofocuserr = $lconf['is_autofocuserr'];
        $af_val = is_string($value_now) ? trim($value_now) : $value_now;
        $autofocus = false;
        if($is_autofocus === true) {
            if($has_error === true && $is_autofocuserr === true) {
                $autofocus = true;
            }
            else if($is_required === true && empty($af_val)) {
                $autofocus = true;
            }
        }
        $_ELM['_af'] = $autofocus;

        $_ELM['valu_feedback_allowed'] = $lconf['feedback_allowed'] ?? [];
        $_ELM['valu_type']           = $lconf['type'] ?? '';
        $_ELM['valu_name']           = $lconf['name'];
        $_ELM['valu_label']          = $lconf['label'];
        $_ELM['valu_class']          = $lconf['class'];
        $_ELM['valu_errors']         = $errors[$_ELM['valu_name']] ?? [];
        $_ELM['valu_errorfirst']     = $_ELM['valu_errors'] ?? '';  #untested
        $_ELM['valu_maxlength']      = $lconf['max'] ?? null;
        $_ELM['valu_placeholder']    = $lconf['placeholder'];
        $_ELM['valu_placeholderS2']  = AppFn::STR_NotEmptyEvalSelf($lconf['placeholder'], ' '); // alt code 255
        $_ELM['valu_hinter_lbl']     = $lconf['hinter_lbl'];
        $_ELM['valu_hinter_ipt']     = $lconf['hinter_ipt'];
        $_ELM['valu_preloads']       = $preloads[$lconf['name']] ?? null;

        $_ELM['bool_haserror']       = $has_error;
        $_ELM['bool_required']       = $lconf['is_required'];
        $_ELM['bool_autocomplete']   = $lconf['is_autocomplete'] ?? false;
        $_ELM['bool_autofocus']      = $lconf['is_autofocus'];
        $_ELM['bool_autofocuserr']   = $lconf['is_autofocuserr'];

        $attr_hinter_ipt = 'data-theme="dark" data-trigger="focus" data-html="true" title="'.$_ELM['valu_hinter_ipt'].'"';
        $attr_hinter_lbl = 'data-theme="dark" data-trigger="focus hover" data-html="true" title="'.$_ELM['valu_hinter_lbl'].'"';
        $html_hinter_lbl = '<span data-toggle="tooltip" '.$attr_hinter_lbl.'><i class="mr-1 ml-1 fas fa-question-circle" style="font-size: 14px;"></i></span>';

        $_ELM['attr_maxlength']      = !empty($_ELM['valu_maxlength']) ? 'maxlength="'.$_ELM['valu_maxlength'].'"' : '';
        $_ELM['attr_placeholder']    = !empty($_ELM['valu_placeholder']) ? 'placeholder="'.$_ELM['valu_placeholder'].'"' : '';
        $_ELM['attr_required']       = $_ELM['bool_required'] ? 'required' : '';
        $_ELM['attr_autocomplete']   = 'autocomplete="'.($_ELM['bool_autocomplete'] ? 'on' : 'off').'"';
        $_ELM['attr_autofocus']      = $_ELM['_af'] ? 'autofocus' : '';
        $_ELM['attr_hinter_ipt']     = !empty(trim($_ELM['valu_hinter_ipt'])) ? $attr_hinter_ipt : '';;
        $_ELM['html_required']       = $_ELM['bool_required'] ? '<span class="text-danger" title="Required">*</span>' : '';
        $_ELM['html_hinter_lbl']     = !empty(trim($_ELM['valu_hinter_lbl'])) ? $html_hinter_lbl : '';


        point2:
        return $_ELM;
    }












    public static function CONFIG_env_all() {
        $arr = [];
        try { $arr = require(config_path('env.php'));
        } catch(\Exception $ex) {}
        return (array)$arr;
    }

    public static function CONFIG_env_fetch(array $arr, string $key, $default=null, string $type='') {
        $arr1 = $arr2 = $arr[$key] ?? $default;
        if($type !== '') {
            if($type === 'boolean' || $type === 'bool') {
                $arr2 = ($arr2 === 'true' || $arr2 === true);
            }
            else {
                settype($arr2, $type);
            }
        }
        return $arr2;
    }

    public static function CONFIG_env(string $key, $default=null, string $type='') {
        // App Config values
        // linked to app/env.php
        // do not use env() or even config() in controller, they are inconsistent

        /*$val1 = $val2 = config('env.'.$key, $default);
        if($type !== '') {
            if($type === 'boolean' || $type === 'bool') {
                $val2 = $val2 === 'true';
            }
            else {
                settype($val2, $type);
            }
        }
        return $val2;*/

        /*$arr = SELF::CONFIG_env_all();
        $arr1 = $arr2 = $arr[$key] ?? $default;

        if($type !== '') {
            if($type === 'boolean' || $type === 'bool') {
                $arr2 = $arr2 === 'true';
            }
            else {
                settype($arr2, $type);
            }
        }
        return $arr2;*/

        $arr = SELF::CONFIG_env_all();
        return SELF::CONFIG_env_fetch($arr, $key, $default, $type);
    }

}



