<?php 

namespace Rguj\Laracore\Library;

// ----------------------------------------------------------
//use Illuminate\Http\Request;
use Rguj\Laracore\Request\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Exception;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\CLHF;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\HttpResponse;
use Rguj\Laracore\Library\StorageAccess;
// ----------------------------------------------------------

/**
 * Web Client / User Agent
 */

class WebClient {
    
    public static function getKeys() {
        $arr = [
            'root' => 'webclient',
            'prev_url' => 'prev_url',
            'timezone' => 'timezone',
            'ip_address' => 'ip_address',
            'os' => 'os',
            'browser' => 'browser',
            'device' => 'device',
            'languages' => 'languages',
            'server' => 'server',
        ];
        return $arr;
    }

    protected static function makeSessionKey(string $session_key) {
        Session::put($session_key, []); // create key if not exists
        $isCreated = (!is_null(Session::get($session_key)));
        return $isCreated;
    }

    public static function getClientUAInfo(Request $request) {
        $agent = new Agent();
        $keys = SELF::getKeys();
        $device_type = '';
        $phone_type = '';
        $err_msg = '';
        $clientUAInfo = [];
        $data = [false, '', []];

        try {
            // IPv4
            $ip_address = $request->ip();
            $ip_address = $ip_address === '::1' ? '127.0.0.1' : $ip_address;
            if(!CLHF::VALIDATOR_IPv4($ip_address))
                throw new exception('IP Address is not v4.');

            // TIME ZONE
            $timezone = config('user.settings.timezone') ?? '';
            if(!DT::isTZString($timezone))
                throw new exception('Time zone `'.$timezone.'` is invalid.');

            // PREV URL
            $prev_url = SELF::nextURL();  //$inputs['_next_url'] ?? '';

            // operating system
            $os = ['name' => $agent->platform(),'version' => $agent->version($agent->platform())];

            // browser
            $browser = ['name' => $agent->browser(),'version' => $agent->version($agent->browser())];

            // device type
            if($agent->isDesktop())
                $device_type = 'desktop';
            else if($agent->isPhone())
                $device_type = 'phone';
            else
                throw new exception('Invalid device type.');

            // phone type
            if($device_type == 'phone') {
                if($agent->isMobile())
                    $phone_type = 'mobile';
                else if($agent->isTablet())
                    $phone_type = 'mobile';
                else
                    throw new exception('Invalid phone type.');
            }

            // forming device info array
            $device = [
                'type' => $device_type,
                'name' => $agent->device(),
                'phone_type' => $phone_type,
                'mobile_grade' => $agent->mobileGrade(),
                'is_robot' => $agent->isRobot(),
            ];

            // languages
            $languages = $agent->languages();

            // forming final data
            $clientUAInfo = [
                $keys['ip_address'] => $ip_address,
                $keys['prev_url'] => $prev_url,
                $keys['timezone'] => $timezone,
                $keys['os'] => $os,
                $keys['browser'] => $browser,
                $keys['device'] => $device,
                $keys['languages'] => $languages,
                // $keys['server'] => $server,
            ];

        } catch (\Exception $ex) {
            $err_msg = $ex->getMessage();
            $data[1] = $err_msg;
            goto point1;            
            //dd('Failed to issue web client info. Please try again.');
            //dd($err_msg);
        }

        $data[0] = true;
        $data[2] = $clientUAInfo;
        point1:
        return $data;
    }

    /*public static function issueClientUAInfo(Request $request) {
        $data = [false, ''];
        $keys = SELF::getKeys();

        try {
            $isCreated = SELF::makeSessionKey($keys['root']);
            if($isCreated !== true)
                throw new exception('Failed to issue web client info. Please try again.');

            $webclient_data = SELF::getClientUAInfo($request, config('user.settings.timezone', env('USER_DEFAULT_TIMEZONE')));
            if($webclient_data[0] !== true)
                throw new exception($webclient_data[1]);

            Session::put($keys['root'], $webclient_data[2]);

        } catch(\Exception $ex) {
            $data[1] = $ex->getMessage();
            goto point1;
        }

        $data[0] = true;
        point1:
        return $data;
    }*/
    
    /*public static function hasValidTimeZone() {
        $keys = SELF::getKeys();
        $needle = $keys['root'].'.'.$keys['timezone'];  // webclient.timezone
        $tz = Session::get($needle) ?? '';
        $is_valid_tz = DT::isTZString($tz);
        return $is_valid_tz;
    }*/

    public static function getTimeZone() {
        $keys = SELF::getKeys();
        $needle = $keys['root'].'.'.$keys['timezone'];  // webclient.timezone
        $timezone = Session::get($needle) ?? '';
        $is_valid_tz = DT::isTZString($timezone);
        return $is_valid_tz ? $timezone : '';
    }

    public static function getPrevURL() {
        $keys = SELF::getKeys();
        $needle = $keys['root'].'.'.$keys['prev_url'];  // webclient.prev_url
        $prev_url = Session::get($needle) ?? '';
        $prev_url = (string)(is_string($prev_url) ? $prev_url : '');
        return $prev_url;
    }
    
    public static function getIPAddress() {
        $keys = SELF::getKeys();
        $needle = $keys['root'].'.'.$keys['ip_address'];  // webclient.ip_address
        $ip_address = Session::get($needle) ?? '';
        return CLHF::VALIDATOR_IPv4($ip_address) ? $ip_address : '';
    }

    /*public static function issueTimeZone(Request $request) {
        // this is called via post only

        $data = [false, ''];
        $sk = SELF::getKeys();
        try {
            $timezone = $request->post('timezone') ?? '';
            if(DT::isTZString($timezone) !== true)
                throw new exception('Invalid TZ. Please refresh your browser.');
            $needle = $sk['root'].'.'.$sk['timezone'];
            Session::put($needle, $timezone);

        } catch(\Exception $ex) {
            $data[1] = $ex->getMessage();
            goto point1;
        }
        $data[0] = true;
        $data[1] = 'TZ successfully set';
        point1:
        return $data;
    }*/














    //public static function nextURL(bool $auth_mode=false) {
    public static function nextURL() {
        // returns the previous URI
        // default return is ''

        // get previous url
        $curr_url = url()->current();
        $prev_url = url()->previous();
        $parsed_prev_url = parse_url($prev_url);
        $prev_url_path = $parsed_prev_url['path'] ?? '';
        $except = [  // guest pages
            // route('index.index'),               // /
            route('login'),                     // /login
            route('register'),                  // /register
            // route('auth.fb.redirect'),          // /auth/facebook/redirect
            // route('auth.fb.callback'),          // /auth/facebook/callback
            // route('auth.fb.deletion'),          // /auth/facebook/deletion
        ];
        //$URIs_ignored = in_array($curr_url, $except) ? $except : [];
        $URIs_ignored = $except;
        $bool1 = (!empty($prev_url_path) && !in_array($prev_url_path, $URIs_ignored) && $curr_url !== $prev_url);
        return $bool1 ? $prev_url : '';
    }

    













}



