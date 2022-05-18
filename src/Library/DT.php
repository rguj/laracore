<?php 

namespace Rguj\Laracore\Library;

// ----------------------------------------------------------
use Exception;
use Carbon\Carbon;
// ----------------------------------------------------------

class DT {

    // this class heavily depends on PHP Carbon library (laravel built-in)
    // and uses try/catch to prevent error notices

    // use \App\Traits\DTTrait;


    protected static $dt_standard_format = 'Y-m-d H:i:s.u';
    protected static $tz_standard_value = 'UTC';




    public static function getStandardDTFormat() {
        // DateTime Stamp Standard Format ('Y-m-d H:i:s.u')
        return DT::$dt_standard_format;  
    }

    public static function getStandardTZ() {
        return DT::$tz_standard_value;  // 0:00
    }
    
    public static function getServerTZ() {
        $stz = AppFn::CONFIG_env('APP_TIMEZONE', '', 'string');
        if(DT::isTZString($stz) !== true)
            throw new exception('Invalid server timezone');
        return $stz;
    }

    















    // ----------------------------------------------------------
    // VALIDATORS

    public static function isCarbonObject($obj) {
        // use this function to properly validate DT object
        $bool1 = (!is_null($obj) && ($obj instanceof Carbon) && (get_class($obj)==='Carbon\Carbon'));
        return $bool1;
    }

    public static function isDTSSFormat(string $dtf_str) {
        // Is DateTime Stamp Standard Format ('Y-m-d H:i:s.u')
        // check if format string is equal to the standard format `DT::getStandardDTFormat()`
        return $dtf_str === DT::getStandardDTFormat();
    }

    public static function isDTString(string $dt_format, string $dt_str) {
        // if returns `null`, the error is either on `dt_format` or `dt_str`, or BOTH
        $is_valid = false;
        $dt_test = null;
        try {
            $dt_test = Carbon::createFromFormat($dt_format, $dt_str);
            $is_valid = true;
        } catch (\Exception $ex) {}
        return $is_valid;
    }

    public static function isTZString(string $tz_str) {
        // if returns `null`, the error is either on `dt_format` or `dt_str`, or BOTH
        $is_valid = false;
        $dt_now = null;
        $dt_test = null;
        $dt_format = DT::getStandardDTFormat();
        try {
            $dt_now = Carbon::now(DT::getStandardTZ())->startOfDay()->format($dt_format);
            $dt_test = Carbon::createFromFormat($dt_format, $dt_now, $tz_str);
            $is_valid = true;
        } catch (\Exception $ex) {}
        return $is_valid;
    }










    // ----------------------------------------------------------
    // BUILDERS

    /**
     * Returns current date time as Carbon object. Default timezone is server timezone. Datetime value will change if given timezone differs from the server timezone.
     *
     * @param string $tz_str
     * @return Carbon\Carbon|null
     * @uses Carbon\Carbon
     */
    public static function now(string $tz_str='') {
        $tz_str = AppFn::STR_IsBlankSpace($tz_str) ? 'UTC' : AppFn::STR_Sanitize($tz_str);
        if(DT::isTZString($tz_str) !== true)
            throw new Exception('Invalid timezone');
        $tz_server = DT::getServerTZ();
        $dt_test = Carbon::now($tz_server);
        $dt_test = DT::isCarbonObject($dt_test) ? $dt_test->setTimezone($tz_str) : null;
        return $dt_test;
    }

    /**
     * Returns current date time as string
     *
     * @param string $tz default 'UTC'
     * @param string $format default 'Y-m-d H:i:s.u'
     * @return string
     */
    public static function now_str(string $tz='', string $format='') {
        if(AppFn::STR_IsBlankSpace($tz))       $tz = DT::getStandardTZ();
        if(AppFn::STR_IsBlankSpace($format))   $format = DT::getStandardDTFormat();
        return DT::now($tz)->format($format);
    }











    /**
     * Advanced datetime parse function
     * 
     * @param string $dt_str
     * @param [string|string]|string $dt_format [ $from, $to ]
     * @param [string|string] $tz [ $from, $to ]
     * @return [bool|array|array|array]|false [ isvalid, [ format_from, format_to ], [ tz_from, tz_to ], [ dt_obj_from, dt_obj_to ] ]
     * @uses Carbon\Carbon
     */
    public static function createDateTimeX2(string $dt_str, $dt_format, array $tz) {
        // validate
        $validate_array = function(array $arr) {
            return (
                AppFn::ARRAY_depth($arr) === 1
                && AppFn::ARRAY_GetType($arr) === 'sequential'
                && count($arr) === 2
                && is_string($arr[0]) === true
                && is_string($arr[1]) === true
                && AppFn::STR_IsBlankSpace($arr[0]) !== true
                && AppFn::STR_IsBlankSpace($arr[1]) !== true
            );
        };
        $dtf = DT::EVAL_dateFormat($dt_format);
        //if(!$validate_array($dt_format))
        //    throw new Exception('Invalid array structure `$dt_format`');
        if(!$validate_array($tz))
            throw new Exception('Invalid array structure `$tz`');

        $format_fm = $dtf[0];
        $format_to = $dtf[1];
        $tz_fm = $tz[0];
        $tz_to = $tz[1];
        //$tz_fm = AppFn::STR_IsBlankSpace($tz[0]) ? DT::getStandardTZ() : AppFn::STR_Sanitize($tz[0]);
        //$tz_to = AppFn::STR_IsBlankSpace($tz[1]) ? DT::getStandardTZ() : AppFn::STR_Sanitize($tz[1]);
        
        $output = [false, [], [], [], []];

        $dt = false;
        try { $dt = Carbon::createFromFormat($format_fm, $dt_str, $tz_fm); }
        catch (\Exception $ex) {}
        if(!DT::isCarbonObject($dt)) goto point1;
        $dt2 = $dt->clone()->setTimeZone($tz_to);
        if(!DT::isCarbonObject($dt2)) goto point1;

        $str_fm = $dt->format($format_fm);
        $str_to = $dt2->format($format_to);
        
        $output[0] = true;
        $output[1] = [$format_fm, $format_to];
        $output[2] = [$tz_fm, $tz_to];
        $output[3] = [$dt, $dt2];
        $output[4] = [$str_fm, $str_to];

        point1:
        return $output;
    }
    
    /**
     * Parses datetime string to Carbon object. Returns false if failed.
     *
     * @param string $dt_str
     * @param string $dt_format Default 'Y-m-d H:i:s.u'
     * @param [string,string] $tz
     * @return Carbon\Carbon|false
     */
    public static function createDateTime(string $dt_str, $dt_format=['', ''], array $tz=['', '']) {
        $dtf = DT::EVAL_dateFormat($dt_format);
        $dt_test = DT::createDateTimeX2($dt_str, $dtf, $tz);
        $output = $dt_test[0] ? $dt_test[3][1] : false;  // UTC carbon object
        return $output;
    }    

    /**
     * Returns Carbon object as UTC timezone. $dt_str value will be unchanged.
     *
     * @param string $dt_str
     * @param [string|string]|string $dt_format
     * @param string $tz_str_from Default 'UTC'
     * @return Carbon\Carbon|false
     */
    public static function createDateTimeUTC(string $dt_str, $dt_format=['', ''], string $tz_str_from='UTC') {
        $dtf = DT::EVAL_dateFormat($dt_format);
        //$tz_str_from = AppFn::STR_IsBlankSpace($tz_str_from) ? 'UTC' : $tz_str_from;  // override empty
        $dt_test = DT::createDateTimeX2($dt_str, $dtf, [$tz_str_from, 'UTC']);
        $output = $dt_test[0] ? $dt_test[3][1] : false;  // UTC carbon object
        return $output;
    }

    /**
     * Returns Carbon object. From client timezone to UTC. $dt_str value will change.
     *
     * @param string $dt_str
     * @param [string|string]|string $dt_format
     * @return Carbon\Carbon|false
     */
    public static function createDateTimeUTCFromClient(string $dt_str, $dt_format=['', '']) {
        $tz_client = WebClient::getTimeZone();
        if(DT::isTZString($tz_client) !== true)
            throw new Exception('Invalid client timezone');
        $dtf = DT::EVAL_dateFormat($dt_format);
        $dt_test = DT::createDateTimeX2($dt_str, $dtf, [$tz_client, 'UTC']);
        $output = $dt_test[0] ? $dt_test[3][1] : false;  // UTC carbon object
        return $output;
    }













    

    






    // -----------------------------------
    // STRING

    /**
     * Advanced datetime parse function with configurable date format (from & to), and timezone (from & to). This overrides empty timezone to UTC
     *
     * @param string $dt_str
     * @param [string|string]|string $dt_format [ from, to ] | Both required
     * @param [string|string] $timezone [ from, to ] | Default 'UTC'
     * @return string
     */
    public static function STR_TryParseX(string $dt_str, $dt_format=['', ''], array $timezone=['', '']) {
        $dtf = DT::EVAL_dateFormat($dt_format);
        $dt_test = DT::createDateTimeX2($dt_str, $dtf, $timezone);

        if(AppFn::STR_IsBlankSpace($dtf[0]))
            throw new Exception('Invalid $dt_format[0]');
        if(AppFn::STR_IsBlankSpace($dtf[1]))
            throw new Exception('Invalid $dt_format[1]');
        if(AppFn::STR_IsBlankSpace($timezone[0]))
            throw new Exception('Invalid $timezone[0]');
        if(AppFn::STR_IsBlankSpace($timezone[1]))
            throw new Exception('Invalid $timezone[1]');

        $output = $dt_test[0] ? $dt_test[4][1] : '';
        return $output;
    }

    /**
     * Parses datetime string with configurable timezone (from & to)
     *
     * @param string $dt_str
     * @param [string|string]|string $dt_format [ from, to ] | Default 'Y-m-d H:i:s.u'
     * @param [string|string] $tz [ from, to ]
     * @return string
     */
    public static function STR_TryParse(string $dt_str, $dt_format=['', ''], array $tz=['', '']) {
        $dtf = DT::EVAL_dateFormat($dt_format);
        $output = DT::STR_TryParseX($dt_str, $dtf, $tz);
        return $output;
    }

    /**
     * Parses datetime string to UTC timezone
     *
     * @param string $dt_str
     * @param [string|string]|string $dt_format [ from, to ] | Default 'Y-m-d H:i:s.u'
     * @param string $tz_str_from Default 'UTC'
     * @return string
     */
    public static function STR_TryParseUTC(string $dt_str, $dt_format=['', ''], string $tz_str_from='UTC') {
        $dtf = DT::EVAL_dateFormat($dt_format);        
        $tz_str_from = AppFn::STR_IsBlankSpace($tz_str_from) ? 'UTC' : $tz_str_from;  // override empty
        $output = DT::STR_TryParseX($dt_str, $dtf, [$tz_str_from, 'UTC']);
        return $output;
    }

    /**
     * Parses datetime string from client timezone to UTC
     *
     * @param string $dt_str
     * @param [string|string]|string $dt_format [ from, to ] | Default 'Y-m-d H:i:s.u'
     * @return string
     */
    public static function STR_TryParseUTCFromClient(string $dt_str, $dt_format=['', '']) {
        $tz_client = WebClient::getTimeZone();
        if(DT::isTZString($tz_client) !== true)
            throw new Exception('Invalid client timezone');
        $dtf = DT::EVAL_dateFormat($dt_format);
        $output = DT::STR_TryParseX($dt_str, $dtf, [$tz_client, 'UTC']);
        return $output;
    }

    /**
     * Evaluates two datetime string (in ascending) and returns the first correct value.
     *
     * @param string $dt_format
     * @param string $dt_val
     * @param string $dt_fallback
     * @return string
     */
    public static function STR_TryParseUTCEval(string $dt_format, string $dt_val, string $dt_fallback) {
        $output = '';
        $dt_format = AppFn::STR_IsBlankSpace($dt_format) ? DT::getStandardDTFormat() : $dt_format;
        $dtf = DT::EVAL_dateFormat($dt_format);
        $dt1 = DT::STR_TryParseUTC($dt_val, $dtf);
        $dt2 = DT::STR_TryParseUTC($dt_fallback, $dtf);
        $bool1 = !AppFn::STR_IsBlankSpace($dt1);
        $bool2 = !AppFn::STR_IsBlankSpace($dt2);
        if($bool1 !== true && $bool2 !== true)
            goto point1;
        if($bool1 === true)
            $output = $dt1;
        else if($bool2 === true)
            $output = $dt2;
        point1:
        return $output;
    }

    

    









    // -----------------------------------
    // UTILS

    public static function EVAL_dateFormat($dt_format, bool $strict_mode=false) {
        $output = ['', ''];

        $evaluator = function(string $dt_format) use($strict_mode) {
            $output = [false, '', ''];
            try {
                if($strict_mode === true && AppFn::STR_IsBlankSpace($dt_format) === true)
                    throw new Exception('`$dt_format` must be a filled string');
                $fm1 = AppFn::STR_IsBlankSpace($dt_format) ? DT::getStandardDTFormat() : $dt_format;
                if(!AppFn::STR_IsBlankSpace($fm1))
                    $output[2] = $fm1;
                $output[0] = true;
            } catch(\Exception $ex) {
                $output[1] = $ex->getMessage();
            }
            return $output;
        };

        if(is_string($dt_format)) {
            $eval = $evaluator($dt_format, $strict_mode);
            if($eval[0] !== true)
                throw new Exception($eval[1]);
            $output = [$eval[2], $eval[2]];
        }
        else if(is_array($dt_format)) {
            $count = count($dt_format);
            if($count === 0)
                throw new Exception('`$dt_format` must be a filled array');
            if(AppFn::ARRAY_depth($dt_format) !== 1)
                throw new Exception('`$dt_format` array must have 1 depth');
            if(AppFn::ARRAY_IsTypeSequential($dt_format) !== true)
                throw new Exception('`$dt_format` must be sequential array');
            if(!in_array($count, [1, 2]))
                throw new Exception('`$dt_format` must have 1 or 2 elements');
            if(!is_string($dt_format[0]))
                throw new exception('`$dt_format[0]` must be string');

            $fm1 = $dt_format[0] ?? '';
            $to1 = $dt_format[1] ?? '';

            // dt_from
            $eval2 = $evaluator($fm1);
            if($eval2[0] !== true)
                throw new Exception($eval2[1].' [0]');
            $output[0] = $eval2[2];

            // dt_to
            if($count === 1) {
                $output[1] = $output[0];
            }
            else if($count === 2) {
                $eval3 = $evaluator($to1);
                if($eval3[0] !== true)
                    throw new Exception($eval3[1].' [1]');
                $to2 = $eval3[2];
                $output[1] = !AppFn::STR_IsBlankSpace($to1) ? $to2 : $output[0];
            }
        }
        else {
            throw new exception('`$dt_format` must be string or array');
        }
        return $output;
    }

    protected static function possible_max_value($max_digits) {
        $max_value = '';
        for($x=0; $x<$max_digits; $x++){
            $max_value .= '9';
        }
        return (int) $max_value;
    }

    protected static function strintround(string $strint, int $max_digits) {
        $output_ = '';
        $strint_c = strlen($strint);
        if($strint_c != 6 || !preg_match('/^([0-9]{6})$/u', $strint) || $max_digits <= 0 || $max_digits > $strint_c)
            goto point2;

        /*if($max_digits == $strint_c) {
            $output = $strint;
            goto point2;
        }*/

        $RUT = 5;  // set Round Up Threshold
        $max_value = DT::possible_max_value($max_digits);
        $pcs1 = $pcs2 = array_map('intval', str_split($strint));
        $add = ((int)$pcs1[count($pcs1)-1] >= $RUT) ? 1 : 0;

        for($x=count($pcs1)-1; $x>=0; $x--) {  // round off properly
            $curr_val = $pcs2[$x] + $add;
            $add = ($curr_val >= $RUT) ? 1 : 0;
            $pcs2[$x] = ($curr_val >= $RUT) ? 0 : $curr_val;
        }
        $pcs3 = array_slice($pcs2, 0, $max_digits);
        foreach($pcs3 as $key=>$val)
            $output_ .= (string)$val;
        //$output = str_pad((string)$output_, $max_digits, '0', STR_PAD_LEFT);

        point2:
        $output = str_pad((string)$output_, $max_digits, '0', STR_PAD_LEFT);
        return $output;
    }

    public static function translateDTtoUN(string $dt_str) {
        // -- translates dt_str to a unique number for ordering
        // -- ONLY ACCEPTED FORMAT (Y-m-d H:i:s.u)
        // -- does not modify the timezone
        // -- returns empty array if there's an error on `dt_str` format
        // -- accepted range: (0000-01-01 00:00:00.000000 - 9999-12-31 23:59:59.999999) + parse validation
        
        $output = [];
        $__DT_FORMAT = DT::getStandardDTFormat();
        $err_msg = '';

        try {
            // validate datetime range
            $regex1 = '/^([0-9]{4})\-(0[1-9]|1[0-2])\-(0[1-9]|[12][0-9]|3[01])\ (0[0-9]|1[0-9]|2[0-3])\:([0-5][0-9])\:([0-5][0-9])\.([0-9]{6})$/u';
            if(!preg_match($regex1, $dt_str))
                throw new exception('Datetime string does not match the format.');

            // try parse datetime string
            $dt_test = DT::createDateTimeUTC($dt_str, $__DT_FORMAT, DT::getStandardTZ());
            if(DT::isCarbonObject($dt_test) !== true) 
                throw new exception('Failed to parse datetime string.');

            $str_year = substr($dt_str, 0, 4);
            $str_month = substr($dt_str, 5, 2);
            $str_day = substr($dt_str, 8, 2);
            $str_hour = substr($dt_str, 11, 2);
            $str_minute = substr($dt_str, 14, 2);
            $str_second = substr($dt_str, 17, 2);
            $str_microsecond = substr($dt_str, 20, 6);

            $year = (int) $str_year;
            $month = (int) $str_month;
            $day = (int) $str_day;
            $hour = (int) $str_hour;
            $minute = (int) $str_minute;
            $second = (int) $str_second;
            $microsecond = (int) $str_microsecond;

            $f_year = ($year);
            $f_month = ($month * 31);
            $f_day = ($day);
            $f_hour = ($hour * 60 * 60);
            $f_minute = ($minute * 60);
            $f_second = ($second) + 0;
            $f_microsecond = $str_microsecond;

            $answer_date_p1 = str_pad((string)($f_year), 4, '0', STR_PAD_LEFT);  // string
            $answer_date_p2 = str_pad((string)($f_month + $f_day), 3, '0', STR_PAD_LEFT);  // string
            $answer_time= ($f_hour + $f_minute + $f_second);

            $portion_rand = str_pad((string)rand(0,999999), 6, '0', STR_PAD_LEFT);  // random 6 numbers
            $portion_date = $answer_date_p1.$answer_date_p2;  // year, month, day
            $portion_time = str_pad($answer_time, 5, '0', STR_PAD_LEFT);  // hour, minute, second
            $portion_mcsc = $f_microsecond;  // 6 digits microseconds
            $portion_mcsc_1 = substr($portion_mcsc, 0, 1);  // first digit in microseconds
            $portion_mcsc_3 = substr($portion_mcsc, 0, 3);  // first digit in microseconds
            $portion_rand_3 = substr($portion_rand, 0, 3);  // random 3 numbers

            $un13 = $portion_date.$portion_time.$portion_mcsc_1;
            $un13_d = $portion_date.'-'.$portion_time.'-'.$portion_mcsc_1;
            $un15 = $portion_date.$portion_time.$portion_mcsc_3;
            $un15_d = $portion_date.'-'.$portion_time.'-'.$portion_mcsc_3;
            $un18 = $portion_date.$portion_time.$portion_mcsc;
            $un18_d = $portion_date.'-'.$portion_time.'-'.$portion_mcsc;
            $un21 = $portion_date.$portion_time.$portion_mcsc.$portion_rand_3;
            $un21_d = $portion_date.'-'.$portion_time.'-'.$portion_mcsc.'-'.$portion_rand_3;
            $un24 = $portion_date.$portion_time.$portion_mcsc.$portion_rand;
            $un24_d = $portion_date.'-'.$portion_time.'-'.$portion_mcsc.'-'.$portion_rand;

            $output = [
                'un13' => $un13,
                'un13_d' => $un13_d,
                'un15' => $un15,
                'un15_d' => $un15_d,
                'un18' => $un18,
                'un18_d' => $un18_d,
                'un21' => $un21,
                'un21_d' => $un21_d,
                'un24' => $un24,
                'un24_d' => $un24_d,
            ];

        } catch(\Exception $ex) {
            $err_msg = $ex->getMessage();
            dd($err_msg);
        }

        point1:
        //dd($output);
        return $output;
    }


    public static function decimal_to_time(float $decimal) {
        $lz = function ($num) {
            return (strlen($num) < 2) ? "0{$num}" : $num;
        };
        // start by converting to seconds
        $seconds = ($decimal * 3600);
        // we're given hours, so let's get those the easy way
        $hours = floor($decimal);
        // since we've "calculated" hours, let's remove them from the seconds variable
        $seconds -= $hours * 3600;
        // calculate minutes left
        $minutes = floor($seconds / 60);
        // remove those from seconds as well
        $seconds -= $minutes * 60;
        $seconds = number_format($seconds, 6, '.', '');
        // return the time formatted HH:MM:SS
        $opt = $lz($hours).":".$lz($minutes).":".$lz($seconds);
        return $opt;
    }




}



