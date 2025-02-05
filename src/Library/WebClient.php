<?php

namespace Rguj\Laracore\Library;

//use Illuminate\Http\Request;
use Rguj\Laracore\Request\Request;
use Jenssegers\Agent\Agent;
use Exception;

/**
 * Web Client / User Agent
 *
 */
class WebClient {

    const DEFAULT_VALUES = [
        'agent' => null,
        'os' => ['', ''],
        'browser' => ['', ''],
        'device' => [
            'type' => '',
            'name' => '',
            'phone_type' => '',
            'mobile_grade' => '',
            'is_desktop' => false,
            'is_phone' => false,
            'is_mobile' => false,
            'is_robot' => false,
        ],
        'languages' => [],

        'prev_url' => '',
        'ip_address' => '',
        'timezone' => '',
        'server' => '',
    ];

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
            'server_name' => 'server_name',
            'server_addr' => 'server_addr',
            'agent' => 'agent',
        ];
        return $arr;
    }

    // protected static function makeSessionKey(string $session_key) {
    //     session()->put($session_key, []); // create key if not exists
    //     $isCreated = (!is_null(session()->get($session_key)));
    //     return $isCreated;
    // }





    public static function __getUA()
    {
        $agent = new Agent();
        $clientUAInfo = SELF::DEFAULT_VALUES;

        try {
            // operating system
            $clientUAInfo['os'] = ['name' => $agent->platform(),'version' => $agent->version($agent->platform())];

            // browser
            $clientUAInfo['browser'] = ['name' => $agent->browser(),'version' => $agent->version($agent->browser())];

            // device type
            if($agent->isDesktop())
                $clientUAInfo['device']['type'] = 'desktop';
            else if($agent->isPhone())
                $clientUAInfo['device']['type'] = 'phone';
            // else
            //     throw new exception('Invalid device type.');

            // phone type
            if($clientUAInfo['device']['type'] == 'phone') {
                if($agent->isMobile())
                    $clientUAInfo['device']['phone_type'] = 'mobile';
                else if($agent->isTablet())
                    $clientUAInfo['device']['phone_type'] = 'mobile';
                // else
                //     throw new exception('Invalid phone type.');
            }

            $clientUAInfo['device']['name'] = $agent->device();
            $clientUAInfo['device']['mobile_grade'] = $agent->mobileGrade();
            $clientUAInfo['device']['is_desktop'] = $agent->isDesktop();
            $clientUAInfo['device']['is_phone'] = $agent->isPhone();
            $clientUAInfo['device']['is_mobile'] = $agent->isMobile();
            $clientUAInfo['device']['is_robot'] = $agent->isRobot();

            // languages
            $clientUAInfo['languages'] = $agent->languages();
            $clientUAInfo['agent'] = $agent;

        } catch(Exception $ex) {

        }

        return $clientUAInfo;
    }


    public static function getClientUAInfo(Request $request = null, bool $throw = true) {
        $ua = SELF::__getUA();

        // 'prev_url' => '',
        // 'ip_address' => '',
        // 'timezone' => '',
        // 'server' => '',

        $data = [false, '', []];

        try {
            $ua['server_name'] = request()->server('SERVER_NAME');
            $ua['server_addr'] = request()->server('SERVER_ADDR');

            // IPv4
            $ip_address = !is_null($request) ? $request->ip() : $_SERVER['REMOTE_ADDR'];
            $ip_address = $ip_address === '::1' ? '127.0.0.1' : $ip_address;
            if($throw && !validate_ipv4($ip_address))
                throw new exception('IP Address is not v4.');
            $ua['ip_address'] = $ip_address;

            // TIME ZONE
            $timezone = (string)(config('z.user.settings.timezone') ?? '');
            if($throw && !dt_is_timezone($timezone))
                throw new exception('Time zone `'.$timezone.'` is invalid.');
            $ua['timezone'] = $timezone;

            // PREV URL
            $ua['prev_url'] = SELF::nextURL();

            $data[0] = true;
            $data[2] = $ua;
        } catch(Exception $ex) {
            $data[1] = $ex->getMessage();
        }

        return $data;
    }



    // public static function getClientUAInfo2(Request $request = null) {
    //     $agent = new Agent();
    //     $keys = SELF::getKeys();
    //     $device_type = '';
    //     $phone_type = '';
    //     $err_msg = '';
    //     $clientUAInfo = [];
    //     $data = [false, '', []];

    //     try {

    //         // IPv4
    //         $ip_address = !is_null($request) ? $request->ip() : $_SERVER['REMOTE_ADDR'];
    //         $ip_address = $ip_address === '::1' ? '127.0.0.1' : $ip_address;
    //         if(!validate_ipv4($ip_address))
    //             throw new exception('IP Address is not v4.');

    //         // TIME ZONE
    //         $timezone = config('user.settings.timezone') ?? '';
    //         // if(!DT::isTZString($timezone))
    //         if(!dt_is_timezone($timezone))
    //             throw new exception('Time zone `'.$timezone.'` is invalid.');

    //         // PREV URL
    //         $prev_url = SELF::nextURL();  //$inputs['_next_url'] ?? '';

    //         // operating system
    //         $os = ['name' => $agent->platform(),'version' => $agent->version($agent->platform())];

    //         // browser
    //         $browser = ['name' => $agent->browser(),'version' => $agent->version($agent->browser())];

    //         // device type
    //         if($agent->isDesktop())
    //             $device_type = 'desktop';
    //         else if($agent->isPhone())
    //             $device_type = 'phone';
    //         else
    //             throw new exception('Invalid device type.');

    //         // phone type
    //         if($device_type == 'phone') {
    //             if($agent->isMobile())
    //                 $phone_type = 'mobile';
    //             else if($agent->isTablet())
    //                 $phone_type = 'mobile';
    //             else
    //                 throw new exception('Invalid phone type.');
    //         }

    //         // forming device info array
    //         $device = [
    //             'type' => $device_type,
    //             'name' => $agent->device(),
    //             'phone_type' => $phone_type,
    //             'mobile_grade' => $agent->mobileGrade(),
    //             'is_desktop' => $agent->isDesktop(),
    //             'is_phone' => $agent->isPhone(),
    //             'is_mobile' => $agent->isTablet(),
    //             'is_robot' => $agent->isRobot(),
    //         ];

    //         // languages
    //         $languages = $agent->languages();

    //         // forming final data
    //         $clientUAInfo = [
    //             $keys['ip_address'] => $ip_address,
    //             $keys['prev_url'] => $prev_url,
    //             $keys['timezone'] => $timezone,
    //             $keys['os'] => $os,
    //             $keys['browser'] => $browser,
    //             $keys['device'] => $device,
    //             $keys['languages'] => $languages,
    //             $keys['agent'] => $agent,
    //             // $keys['server'] => $server,
    //         ];

    //     } catch (\Exception $ex) {
    //         $err_msg = $ex->getMessage();
    //         $data[1] = $err_msg;
    //         // dd($err_msg);
    //         goto point1;
    //         //dd('Failed to issue web client info. Please try again.');
    //     }

    //     $data[0] = true;
    //     $data[2] = $clientUAInfo;
    //     point1:
    //     return $data;
    // }


    // public static function getTimeZone() {
    //     $keys = SELF::getKeys();
    //     $needle = $keys['root'].'.'.$keys['timezone'];  // webclient.timezone
    //     $timezone = session()->get($needle) ?? '';
    //     $is_valid_tz = dt_is_timezone($timezone);
    //     return $is_valid_tz ? $timezone : '';
    // }

    // public static function getPrevURL() {
    //     $keys = SELF::getKeys();
    //     $needle = $keys['root'].'.'.$keys['prev_url'];  // webclient.prev_url
    //     $prev_url = session()->get($needle) ?? '';
    //     $prev_url = (string)(is_string($prev_url) ? $prev_url : '');
    //     return $prev_url;
    // }

    // public static function getIPAddress() {
    //     $keys = SELF::getKeys();
    //     $needle = $keys['root'].'.'.$keys['ip_address'];  // webclient.ip_address
    //     $ip_address = session()->get($needle) ?? '';
    //     return validate_ipv4($ip_address) ? $ip_address : '';
    // }









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
            route(config('env.ROUTE_LOGIN')),                     // /login
            route(config('env.ROUTE_REGISTER')),                  // /register
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



