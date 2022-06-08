<?php 

namespace Rguj\Laracore\Library;

// ----------------------------------------------------------
use App\Providers\RouteServiceProvider;
//use Illuminate\Http\Request;
use Rguj\Laracore\Request\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\User;
use Exception;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\HttpResponse;
use Rguj\Laracore\Library\StorageAccess;
use Rguj\Laracore\Library\WebClient;
// ----------------------------------------------------------

class CLHF {

    // Custom Laravel Helper Functions (CLHF)












    # ----------------------------------------------------------
    # \ VIEWS
    # ----------------------------------------------------------

    public static function VIEW_PageTitle(string $page_title, bool $include_appname=true) {
        $first = trim($page_title);
        $first = !empty($first) ? $first : '~ Untitled';
        $last = trim($include_appname ? AppFn::CONFIG_env('APP_NAME', '', 'string') : '');
        $last = !empty($last) ? ' | '.$last : '';
        $output = $first.$last;
        return $output;
    }

    public static function VIEW_hasSection(string $section, bool $consider_empty=false) {
        // upgraded version of View::hasSection, can check if empty value

        if(View::hasSection($section)) {
            //$content = $env->yieldContent($section);
            $content = View::getSection($section);
            $is_empty = !empty(trim($content));
            return $consider_empty ? true : $is_empty;
        }
        return false;
    }

    public static function VIEW_getSection(string $section, bool $consider_empty=false) {
        // pls use is_null() to check the returning value

        $bool1 = CLHF::VIEW_hasSection($section, $consider_empty);
        return $bool1 ? View::getSection($section) : null;
    }

    public static function VIEW_Render(Request $request, $vw_rdr, $with, int $guard_mode, string $caller_class) {

        // $vw_rdr => view or redirect object
        //$with2 = $with;
        
        // get auth roles     
        $authed_roles = [];
        try { $authed_roles = $caller_class::$authed_roles;
        } catch(\Exception $ex) {}
        
        $user_id = CLHF::AUTH_UserID();

        $renderer = function($redirect) use($request, $with) {
            $data = [null, null, ''];  // [default, object_data, html_string]

            if(is_string($redirect)) {
                // check if string is html or view_name
                if(preg_match('/<\s?[^\>]*\/?\s?>/i', $redirect)) {  // if html string
                    $data = [null, null, $redirect];
                } else {  // if view_name
                    $v = View::make($redirect)->with($with);
                    $data = [null, $v, $v->render()];
                }
                
            }
            else if(get_class($redirect) === 'Illuminate\Http\RedirectResponse') {
                $data = [null, $redirect, $redirect->content()];
            }
            else if(get_class($redirect) === 'Illuminate\View\View') {
                $redirect2 = $redirect->with($with);
                $data = [null, $redirect2, $redirect2->render()];
            } else {
                throw new exception('Could not understand `$redirect`');
            }

            // intelligent picker
            //$data[0] = $request->ajax() ? $data[2] : $data[1];
            $data[0] = $request->ajax() ? 2 : 1;
            return $data;
        };

        // GUARD MODE
        switch($guard_mode) {
            case 0:  // no guard
                //  PASS
            break;
            case 1:  // guest guard
                $guard = CLHF::AUTH_PageGuardGuest($request, false);
                if($guard !== true) {
                    return $renderer($guard);
                }
            break;
            case 2:  // auth guard
                $guard = CLHF::AUTH_PageGuardAuth($request, $authed_roles, $user_id, false);
                if($guard !== true) {                    
                    //$with2['with']['current_user']['alerts_popup'][] = ['status'=>'Unauthorized access'];
                    return $renderer($guard);
                }
            break;
            default:
                throw new exception('$guard_mode must be 0, 1, or 2');
            break;
        }
        return $renderer($vw_rdr);
    }

    public static function VIEW_RenderHIE(string $name, string $value) {
        // Render Hidden Input Element
        return '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
    }

    public static function VIEW_RenderHIEPurpose(string $value, bool $is_value_md5=false) {
        $val = ($is_value_md5 ? $value : MD5($value));
        return CLHF::VIEW_RenderHIE('_purpose', $val);
        //return '<input type="hidden" name="_purpose" value="'.$val.'" />';
    }

    public static function VIEW_ErrorResponse(int $code, string $title, string $detail, string $prev_url='') {
        $data = ['data'=>['code'=>$code,'title'=>$title,'detail'=>$detail,'prev_url'=>$prev_url]];
        $view = view('errors.error')->with($data);
        //dd($view);
        return $view;
    }

    public static function VIEW_validateComponentRequisite(array $config, array $UID, int $check_mode=0) {
        $output = [false, ''];
        try {
            if(!in_array($check_mode, [0, 1, 2]))  // [all, config only, uid only]
                throw new exception('$check_mode must be 0, 1, or 2');
            
            // config & UID
            $bool1 = (
                AppFn::ARRAY_depth($config) >= 1
                && array_key_exists('name', $config) && !AppFn::STR_IsBlankSpace($config['name'])
                && array_key_exists('label', $config) && is_string($config['label'])
                && array_key_exists('description', $config) && is_string($config['description'])
                && array_key_exists('placeholder', $config) && is_string($config['placeholder'])
                && array_key_exists('class', $config) && is_string($config['class'])
                && array_key_exists('hinter_lbl', $config) && is_string($config['hinter_lbl'])
                && array_key_exists('hinter_ipt', $config) && is_string($config['hinter_ipt'])
                && array_key_exists('is_required', $config) && is_bool($config['is_required'])
                && array_key_exists('is_autocomplete', $config) && is_bool($config['is_autocomplete'])
                && array_key_exists('is_autofocus', $config) && is_bool($config['is_autofocus'])
                && array_key_exists('is_autofocuserr', $config) && is_bool($config['is_autofocuserr'])
                && array_key_exists('feedback_allowed', $config) && is_array($config['feedback_allowed'])
            );
            $bool2 = (
                AppFn::ARRAY_depth($UID) >= 1
                && array_key_exists('rules', $UID)
                && array_key_exists('preloads', $UID)
                && array_key_exists('values', $UID)
                && array_key_exists('errors', $UID)
                
                // && array_key_exists('rules', $UID)
                // && array_key_exists('preloads', $UID)
                // && array_key_exists('values', $UID)
                // && array_key_exists('errors', $UID)
            );
            
            if(!$bool1 && in_array($check_mode, [0, 1])) throw new Exception('Invalid array structure `$config`');
            if(!$bool2 && in_array($check_mode, [0, 2])) throw new Exception('Invalid array structure `$uid`');
            $output[0] = true;
        } catch(\Exception $ex) {
            $output[1] = $ex->getMessage();
        }        
        return $output;
    }

    //public static function VIEW_componentAnalysis(string $componentName, $attributes, $slot, $args) {
    public static function VIEW_componentAnalysis($data, $args) {

        // $data component data
        // $args [config[...], form[preloads, fieldrules, defaults, errors, ]]
        
        // can accept one or multiple configs

        if(!is_array($args) || count($args) !== 2)
            throw new Exception('$args must be an array of 2 (config & with)');
        
        $configs = $args[0];
        $form = $args[1]['form'];//$args[1];
                
        $APP_VSK_NAME = AppFn::CONFIG_env('APP_VSK_NAME', '', 'string');
        if(AppFn::STR_IsBlankSpace($APP_VSK_NAME))
            throw new Exception('VSK value is empty');

        if(empty($configs))
            throw new exception('Must have at least 1 config array');

        $componentName = $data['componentName'];
        $attributes = AppFn::OBJECT_toArray($data['attributes'])['attributes'];
        $slot = AppFn::OBJECT_toArray($data['slot'])['html'];

        $PRELOADS   = $form['preloads'] ?? [];
        $RULES      = $form['rules'] ?? [];
        $VALUES     = $form['values'] ?? [];
        $ERRORS     = $form['errors'] ?? [];

        // $PRELOADS   = $form['field_preloads'] ?? [];
        // $RULES      = $form['field_rules'] ?? [];
        // $VALUES     = $form['field_values'] ?? [];
        // $ERRORS     = $form['field_errors'] ?? [];

        $ANALYZER = function(int $num, array $config) use($componentName, $attributes, $slot, $form, $ERRORS, $PRELOADS, $RULES, $VALUES, $APP_VSK_NAME) {

            // validate config and form (User Interaction Data)
            // dd($form);
            $validation = CLHF::VIEW_validateComponentRequisite($config, $form);
            if(!$validation[0])
                throw new exception($validation[1].' ['.$num.']');
                                    
            //$config2['componentName_'] = $componentName;
            //$config2['attributes_'] = AppFn::OBJECT_toArray($attributes)['attributes'];
            //$config2['slot_'] = AppFn::OBJECT_toArray($slot)['html'];
            $config2['name'] = $config['name'];
            $config2['min'] = $RULES[$config['name']]['min'] ?? 0;
            $config2['max'] = $RULES[$config['name']]['max'] ?? 0;
            $config2['value'] = old($config2['name'], $VALUES[$config2['name']] ?? null);  // INTELLIGENT VALUE PICKER
            $config2['preloads'] = $PRELOADS[$config['name']] ?? [];

            $config2['label'] = $config['label'];
            $config2['description'] = $config['description'];
            $config2['placeholder'] = $config['placeholder'];
            $config2['placeholder_s2'] = AppFn::STR_NotEmptyEvalSelf($config2['placeholder'], ' '); // alt code 255
            $config2['class'] = $config['class'];
            $config2['class_fgs'] = '';  // form group state | override below
            $config2['feedback_allowed'] = $config['feedback_allowed'];

            $config2['bool'] = [    
                'is_valid'             => !array_key_exists($config['name'], $ERRORS),        
                'is_required'          => $config['is_required'],
                'is_autocomplete'      => $config['is_autocomplete'],
                'is_autofocus'         => $config['is_autofocus'],  // override below
                'is_autofocuserr'      => $config['is_autofocuserr'],
                'is_showable_error'    => in_array('error', $config['feedback_allowed']),
                'is_showable_success'  => in_array('success', $config['feedback_allowed']),            
            ];


            // ---------------------------------------------------
            // FUNCTIONS

            $fgs_picker = function(string $attr_name) use($APP_VSK_NAME, $config2) {
                // form group state picker
                $vsk = old($APP_VSK_NAME.'.'.$attr_name, '');
                $fg_state = 'fg-normal';
                if($vsk === 'error')
                    $fg_state = ($config2['bool']['is_showable_error'] ? 'fg-error' : 'fg-normal');
                else if($vsk === 'success')
                    $fg_state = ($config2['bool']['is_showable_success'] ? 'fg-success' : 'fg-normal');            
                return $fg_state;
            };

            $is_af = function($val) use($config2) {
                $af_val = is_string($val) ? AppFn::STR_Sanitize($val) : $val;
                $autofocus = false;
                if($config2['bool']['is_autofocus'] === true) {
                    if($config2['bool']['is_valid'] !== true && $config2['bool']['is_autofocuserr'] === true) {
                        $autofocus = true;
                    }
                    else if($config2['bool']['is_required'] === true && empty($af_val)) {
                        $autofocus = true;
                    }
                }
                return $autofocus;
            };


            // ---------------------------------------------------
            // REFORM ATTRIBUTE
            //$config2['attributes_']['descriptionStyle'] = array_key_exists('descriptionStyle', $config2['attributes_']) ? $config2['attributes_']['descriptionStyle'] : '';
            //$config2['attributes_']['elementStyle'] = array_key_exists('elementStyle', $config2['attributes_']) ? $config2['attributes_']['elementStyle'] : '';


            // ---------------------------------------------------
            // LOGIC
            
            $config2['bool']['is_autofocus'] = $is_af($config2['value']);
            $config2['class_fgs'] = $fgs_picker($config['name']);

            // HINTER
            $components1 = ['forms.input'];  // allowed for max length
            $hinter_msg = $config2['bool']['is_required'] ? 'Required' : 'Optional';
            if(in_array($componentName, $components1))
                $hinter_msg .= '<br>Max length is '.$config2['max'].'';
            $hinter_msg .= !AppFn::STR_IsBlankSpace($config['hinter_lbl']) ? '<br>'.$config['hinter_lbl'] : '';
            $config2['hinter'] = [
                'label' => $hinter_msg,
                'input' => $hinter_msg,
            ];

            $msg_error = array_key_exists($config['name'], $ERRORS) ? ($ERRORS[$config['name']][0] ?? 'ERROR') : '';
            $config2['msg'] = [
                'current'  => $config2['bool']['is_valid'] ? $config['description'] : $msg_error,  // INTELLIGENT MSG PICKER
                'default'  => $config['description'],
                'error'    => $msg_error,
            ];        

            $attr_hinter_ipt = 'data-theme="dark" data-trigger="focus" data-html="true" title="'.$config2['hinter']['input'].'"';
            $attr_hinter_lbl = 'data-theme="dark" data-trigger="focus hover" data-html="true" title="'.$config2['hinter']['label'].'"';
            $html_hinter_lbl = '<span data-toggle="tooltip" '.$attr_hinter_lbl.'><i class="mr-1 ml-1 fas fa-question-circle" style="font-size: 14px;"></i></span>';
            $html_required = $config2['bool']['is_required'] ? '<span class="text-danger" title="Required">*</span>' : '';

            
            $config2['attr'] = [
                'maxlength'      => $config2['max']>0 ? 'maxlength='.$config2['max'].'' : '',
                'placeholder'    => !empty($config2['placeholder']) ? 'placeholder="'.$config2['placeholder'].'"' : '',
                'required'       => $config2['bool']['is_required'] ? 'required' : '',
                'autocomplete'   => 'autocomplete='.($config2['bool']['is_autocomplete'] ? 'on' : 'off').'',
                'autofocus'      => $config2['bool']['is_autofocus'] ? 'autofocus' : '',
                'hinter_input'   => !AppFn::STR_IsBlankSpace($config2['hinter']['input']) ? $attr_hinter_ipt : '',
                'others'         => '',
                
            ];
            $config2['html'] = [
                'label' => '',
                //'required'       => $html_required,
                //'hinter_label'   => !AppFn::STR_IsBlankSpace($config2['hinter']['label']) ? $html_hinter_lbl : '',
            ];

            // LABEL CONTENT
            $arr2 = [
                ':label'    => $config2['label'], 
                ':hinter'   => $html_hinter_lbl, 
                ':required' => $html_required,
            ];
            $txt1 = $config['label_content'];
            foreach($arr2 as $key=>$val) {
                $txt1 = str_replace($key, $val, $txt1);
            }
            $config2['html']['label'] = $txt1;

            // ATTR OTHERS 
            $arr1 = [
                ':maxlength'    => $config2['attr']['maxlength'], 
                ':required'     => $config2['attr']['required'], 
                ':autocomplete' => $config2['attr']['autocomplete'], 
                ':autofocus'    => $config2['attr']['autofocus'],
            ];
            $txt1 = $config['attr_others'];
            foreach($arr1 as $key=>$val) {
                $txt1 = str_replace($key, $val, $txt1);
            }
            $config2['attr']['others'] = $txt1;

            //if(array_key_exists('element2', $config))
            //    $config2['element2'] = $config['element2'];

            return $config2;
        };

        // start
        $analysis = [];
        //$analysis['componentName_'] = $componentName;
        $analysis['attributes_'] = $attributes;
        $analysis['slot_'] = $slot;
        foreach($configs as $key=>$val) {
            $analysis['elements'][$key] = $ANALYZER($key, $val);
        }

        $data2 = $data;
        unset($data2['args'], $data2['config'], $data2['form']);
        $analysis2 = array_merge($data2, ['args'=>$args], $analysis);
        //dd($analysis2);
        return $analysis2;
    }

    # ----------------------------------------------------------
    # / VIEWS
    # ----------------------------------------------------------


























    # ----------------------------------------------------------
    # \ VALIDATORS
    # ----------------------------------------------------------

    public static function VALIDATOR_URL(string $url) {
        // LARAVEL DEPENDENT
        $validator = Validator::make(
            ['ip' => $url],
            ['ip' => ['required', 'url']]
        );
        return $validator->fails() ? false : true;
    }
    public static function VALIDATOR_IPv46(string $ipv46) {
        // LARAVEL DEPENDENT
        $validator = Validator::make(
            ['ip' => $ipv46],
            ['ip' => 'required|ip']
        );
        return $validator->fails() ? false : true;
    }
    public static function VALIDATOR_IPv4(string $ipv4) {
        // LARAVEL DEPENDENT
        $validator = Validator::make(
            ['ipv4' => $ipv4],
            ['ipv4' => 'required|ipv4']
        );
        return $validator->fails() ? false : true;
    }
    public static function VALIDATOR_IPv6(string $ipv6) {
        // LARAVEL DEPENDENT
        $validator = Validator::make(
            ['ipv6' => $ipv6],
            ['ipv6' => 'required|ipv6']
        );
        return $validator->fails() ? false : true;
    }
    public static function VALIDATOR_email(string $email) {
        /*
            FORMAT:
                username          => A-Z, a-z, 0-9, underscore
                middle character  => @
                domain name       => (A-Za-z0-9-)(.)((A-Za-z0-9-)){2,}
        */

        $FR_App = FieldRules::getGeneral();
        $regex = $FR_App['email']['regex'];
        return AppFn::STR_preg_match($regex, $email);
    }

    public static function VALIDATOR_password(string $password) {
        /*
            FORMAT: A-Z, a-z, 0-9, keyboard symbols
        */
        $FR_App = FieldRules::getGeneral();
        $regex = $FR_App['password']['regex'];
        return AppFn::STR_preg_match($regex, $password);
    }

    public static function VALIDATOR_pname(string $pname) {  # Person name validator

        $FR_App = FieldRules::getGeneral();
        $regex = $FR_App['pname']['regex'];

        /*$test = [
            'John Doe',
            'pedro alberto ch',
            'Ar. Gen',
            'Mathias d\'Arras',
            'Martin Luther King, Jr.',
            'John',
            '',
            '陳大文',
            'John Elkjærd',
            'André Svenson',
            'Marco d\'Almeida',
            'Kristoffer la Cour', 
            'Hans', 
            'H4nn3 Andersen', 
            'Martin Henriksen!', 
        ];
        $test2 = [];
        foreach($test as $key=>$val) {
            $test2[] = [$val, AppFn::STR_preg_match($regex, $val)];
        }*/

        return AppFn::STR_preg_match($regex, $pname);
    }

    public static function VALIDATOR_VSKGenerate(array $data, array $errors_first, array $except_data=[], bool $include_all=false) {
        /*
            Generates Validation State Keys
            Arranges array keys alphabetically
            Supports wildcard values for $except
            Only use this function after you use => CLHF::VALIDATOR_FirstErrors($errors)
        */

        # configs
        //$array_key_prefix = '`_';
        $protected = ['_token', '_purpose', AppFn::CONFIG_env('APP_VSK_NAME', '', 'string')];

        // get except wildcards
        $except_ends_with = [];
        foreach($except_data as $key=>$val) {
            if(strlen($val)>0 && str_ends_with($val, '*')) {
                $except_ends_with[] = substr($val, 0, -1);
            }
        }

        // distincting values
        $except = !$include_all ? array_merge($protected, $except_data) : [];
        $new_data = [];
        foreach($data as $key=>$val) {
            $passed = true;
            foreach($except_ends_with as $key2=>$val2) {
                if(!AppFn::STR_preg_match('/^('.$val2.'.)$/u', $key)) {
                    $passed = false;
                    break;
                } else {
                    //dd($key);
                }
            }
            if(!in_array($key, $except) && $passed===true) {
                //$new_data[$array_key_prefix.$key] = '';
                $new_data[$key] = '';
            }
        }
    
        // update VSK
        $new_vs_keys = [];
        foreach($new_data as $key=>$val) {
            $attribute = $key;
            if(!in_array($attribute, $except)) {
                $new_vs_keys[$key] = array_key_exists($attribute, $errors_first) ? 'error' : 'success';
            }
        }

        ksort($new_vs_keys);
        return $new_vs_keys;
    }

    /*public static function VALIDATOR_VSKUpdate(array $errors, array $vs_keys, array $except=[]) {
        // Update Validation State Keys
        // arranges array key

        $new_vs_keys = [];
        foreach($vs_keys as $key=>$val) {
            $attribute = substr($key, 2);  // strip first 2 chars (`_)
            if(!in_array($attribute, $except)) {
                $new_vs_keys[$key] = array_key_exists($attribute, $errors) ? 'error' : 'success';
            }
        }
        ksort($new_vs_keys);
        return $new_vs_keys;
    }*/

    public static function VALIDATOR_FirstErrors(array $errors) {
        // EXPECTS ARRAY NOT OBJECT INSTANCE
        // supports 1 dimensional array only ([key=>val]+)
        // analyzes and distincts numeric incremental suffixes of array keys
        // arranges array keys alphabetically

        /*$test_errors = [
            'a' => 'qqq',
            'b' => 'www',
            'dis.1' => 'eee',
            'dis.2' => 'rrr',
            'dis.3' => 'ttt',
            'dis.4' => 'yyy',
            'dis.' => 'uuu',
            'dis' => 'iii',
            'c' => 'ooo',
            'dis.5' => 'ppp',
            'd' => 'aaa',
            'e' => 'sss',
            'f' => 'ddd',
            'zzz.3' => 'fff',
            'zzz.4' => 'ggg',
        ];*/

        $new_errors = [];
        ksort($errors);  // to ensure the ascending key order

        // analyze duplicates
        $x = -1;
        $dups = [];
        $exclude_indexes = [];
        foreach($errors as $key1=>$val1) {
            $x++;
            $matches1 = [];
            $count1 = AppFn::STR_preg_match('/^(([a-zA-Z0-9])+(\.){1}(0?|[1-9][0-9]*){1})$/u', $key1, $matches1);
            if($count1 === true) {
                $matches2 = [];
                $count2 = AppFn::STR_preg_match('/^([a-zA-Z0-9])+/u', $key1, $matches2);
                $dups[$matches2[0]][] = $x;
                $exclude_indexes[] = $x;
            }
        }

        // get first error of duplicates
        $errors_duplicates = [];
        foreach($dups as $key1=>$val1) {
            $target_index = $val1[0] ?? false;
            if($target_index===false)
                continue;
            $x = -1;
            foreach($errors as $key2=>$val2) {
                $x++;
                if($target_index===$x) {
                    $matches1 = [];
                    $count1 = AppFn::STR_preg_match('/^([a-zA-Z0-9])+/u', $key2, $matches1);
                    $errors_duplicates[$matches1[0]] = $val2[0];  #untested
                }
            }
        }
        
        // get first error of singulars
        $errors_singulars = [];
        $x = -1;
        foreach($errors['messages'] as $key2=>$val2) {
            $x++;
            if(in_array($x, $exclude_indexes))
                continue;
            $errors_singulars[$key2] = $val2[0];
        }
        
        // COMBINE SINGULARS AND DUPLICATES, AND KEY SORT
        $new_errors = array_merge($errors_singulars, $errors_duplicates);
        ksort($new_errors);

        return $new_errors;
    }

    public static function VALIDATOR_OverrideErrorMessages(array $rules) {
        // OVERRIDE LARAVEL ERROR MESSAGES

        // reserved string that has prefix colon(:)
        $msgo = [  // message override
            'exact' => [
                'required' => 'This is required',
                'email' => 'Invalid format',
                'present' => 'This is must be present',
                'string' => 'Must be string',
                'distinct' => 'Must be distinct',
            ],
            'startsWith' => [
                'required_if' => 'This is required',
                'required_without' => 'This is required',
                'min' => 'Minimum of :min :character',
                'max' => 'Exceeded :max :character',
                'regex' => 'Invalid format',
                'exists' => 'Invalid input',
                'date_format' => 'Invalid date format',
                'in' => 'Invalid value',
                'not_in' => 'Invalid value',
            ],
        ];

        $custom_messages = [];
        $value2_ = [];
        foreach($rules as $key1 => $value1) {
            if(count($value1) > 0) {
                foreach($value1 as $key2=>$value2) {
                    $value2_[] = $value2;

                    // check if not string
                    if(!is_string($value2))
                        continue;

                    // exact
                    if(array_key_exists($value2, $msgo['exact'])) {
                        $custom_messages[$key1.'.'.$value2] = $msgo['exact'][$value2];
                        continue;
                    }

                    // startsWith
                    foreach($msgo['startsWith'] as $key11=>$val11) {
                        if(Str::startsWith($value2, $key11)) {
                            $r = $key11;  // rule
                            $v = (int)Str::of($value2)->ltrim($r.':')->__toString();  // value
                            $msg1 = $msg2 = $val11;

                            // render value
                            $msg2 = Str::replace(':'.$r, $v, $msg2);

                            // pluralize character
                            $msg2 = Str::replace(':character', Str::plural('character', $v), $msg2);

                            // set key value
                            $custom_messages[$key1.'.'.$r] = $msg2;
                            continue;
                        }
                    }
                    
                }
            }
        }
        //dd($value2_);
        return $custom_messages;
    }

    # ----------------------------------------------------------
    # / VALIDATORS
    # ----------------------------------------------------------






    













    # ----------------------------------------------------------
    # \ SESSION
    # ----------------------------------------------------------

    public static function SESSION_Push(string $key, $val) {
        if(session()->get($key) === null)
            session()->put($key, []); // create key if not exists
        session()->push($key, $val);
    }

    public static function SESSION_PushFeedback(string $status, string $msg) {
        /*
            bs_class => [primary, secondary, success, danger, warning, info, light, dark]
        */

        $key = AppFn::CONFIG_env('APP_SESSION_FEEDBACKS_KEY', '', 'string');
        $val = ['type' => $status, 'msg' => $msg];
        CLHF::SESSION_Push($key, $val);
    }

    public static function SESSION_GetFeedbacks(bool $delete_feedbacks_session = false) {
        $key = AppFn::CONFIG_env('APP_SESSION_FEEDBACKS_KEY', '', 'string');
        $feedbacks = session()->get($key) ?? [];
        if($delete_feedbacks_session)
            session()->forget($key);
        return $feedbacks;
    }

    public static function SESSION_GetErrors() {
        $arr1 = [];
        try {
            $arr1 = Session::all()['errors']->getBags()['default']->getMessages();
        } catch(\Exception $ex) {}
        return $arr1;
    }

    # ----------------------------------------------------------
    # / SESSION
    # ----------------------------------------------------------

























    # ----------------------------------------------------------
    # \ AUTH
    # ----------------------------------------------------------

    

    public static function AUTH_check() {
        return (auth()->id() !== null && auth()->check() === true);
    }

    public static function AUTH_UserID() {
        return CLHF::AUTH_check() ? auth()->id() : false;
    }

    public static function AUTH_UserExists($user_id, bool $check_validity=false) {
        $bool1 = false;
        if(!is_int($user_id))
            goto point1;
        $arr1 = CLHF::DB_LookUp('users', ['id'=>$user_id], true)[0] ?? [];
        if(!empty($arr1)) {
            $arr2 = CLHF::DB_LookUp('user_state', ['user_id'=>$user_id], true)[0] ?? [];
            if($check_validity) {
                $bool1 = (($arr2['is_active'] ?? 0) === 1); 
            } else {
                $bool1 = true;
            }
        }
        point1:
        return $bool1;
    }

    public static function AUTH_UserExists2($user_id) {
        $output = [false, false, false, false];  // [user_exists, is_active, is_verified, passed_all]
        if(!is_int($user_id))
            goto point1;
        $user_data = CLHF::DB_LookUp('users', ['id'=>$user_id], true);
        $user_count = count($user_data);
        $dt_now = DT::now('UTC');
        if($user_count > 0) {
            $output[0] = true;

            // user state
            $user_state_data = CLHF::DB_LookUp('user_state', ['user_id'=>$user_id], true)[0] ?? [];
            $user_state = (($user_state_data['is_active'] ?? 0) === 1);
            if($user_state === true) {
                $output[1] = true;
            }

            // user email verified (column `verified_at`)
            //$user_ev_code = $user_ev_data['code'] ?? '';
            $user_ev_data = CLHF::DB_LookUp('user_emailverify', ['user_id'=>$user_id], true)[0] ?? [];
            $user_ev = DT::createDateTimeUTC(($user_ev_data['verified_at'] ?? ''));
            $valid_va_dt = DT::isCarbonObject($user_ev) ? ($user_ev <= $dt_now) : false;

            if($valid_va_dt === true) {
                $output[2] = true;
            }
            $output[3] = ($output[0]===true && $output[1]===true && $output[2]===true);
        }
        point1:
        return $output;
    }
  
    /**
     * Get the user data
     *
     * @param mixed $user_id
     * @return array|null
     */
    public static function AUTH_UserData($user_id) {
        $FR_App = FieldRules::getGeneral();
        $data = null;
        try {
            $arr1 = \App\Models\User::where('id', '=', $user_id)->first()->toArray();  // CLHF::DB_LookUp('users', ['id'=>$user_id], true)[0];
            $id = $arr1['id'];
            $email = $arr1['email'];
            
            $str = $arr1['auth_with'] ?? '';
            $auth_with = [];
            if(AppFn::STR_preg_match($FR_App['auth_with']['regex'], $str)) {
                $auth_with = explode('_', $str, 2);
            }
            
            // $is_active = (CLHF::DB_LookUp('user_state', ['user_id'=>$user_id], true)[0]['is_active'] ?? 0) === 1;
            $is_active = (bool)DB::table('user_state')->where('user_id', '=', $user_id)->first()->is_active; // (CLHF::DB_LookUp('user_state', ['user_id'=>$user_id], true)[0]['is_active'] ?? 0) === 1;
            
            // $user_roles = CLHF::AUTH_UserRoles($user_id);
            $user_roles = array_column(AppFn::OBJECT_reflect(DB::table('users')
                ->select('user_roles.role')
                ->leftJoin('user_type', 'user_type.user_id', '=', 'users.id')
                ->leftJoin('user_roles', 'user_roles.id', '=', 'user_type.user_role_id')
                ->where('users.id', '=' , auth()->user()->id)
                ->get()->toArray()), 'role');
            
            // at
            $created_at = DT::STR_TryParseUTC(($arr1['created_at'] ?? '').'.000000');
            $updated_at = DT::STR_TryParseUTC(($arr1['updated_at'] ?? '').'.000000');
            $email_verified_at = DT::STR_TryParseUTC(($arr1['email_verified_at'] ?? '').'.000000');
            
            $data = [
                'id' => $id,
                'email' => $email,
                'is_active' => $is_active,
                'is_verified' => !empty($email_verified_at),
                'roles' => $user_roles,
                'auth_with' => $auth_with,
                'at' => [
                    'created' => $created_at,
                    'updated' => $updated_at,
                    'email_verified' => $email_verified_at,
                ],
            ];

        } catch(\Exception $ex) {
            $data = null;
            $err_msg = $ex->getMessage();//.' | '.$ex->getLine();
            //dd($err_msg);
            goto point1;
        }

        point1:
        return $data;
    }

    public static function AUTH_PageGuardGuest(Request $request, bool $popup_error=true) {
        /*
            if already logged in, go back to default homepage depending on user role

            Usage:

                // GUEST GUARD
                $guard = CLHF::AUTH_PageGuardGuest($request);
                if($guard[0] !== true) {
                    return $guard[1];
                }
        */
        $data = [false, null];
        $users_count = CLHF::DBO('users')->count();
        $force_register = AppFn::CONFIG_env('APP_NO_USER_FORCE_REGISTER', false, 'boolean');
        $is_url_registration = (url()->current() === route('register'));
        //$bypass = [route('webclient.ua.issue'), ];
		$bypass = [];

        if(in_array($request->url(), $bypass)) {
            $data[0] = true;
            goto point1;
        }

        if($force_register === true && $users_count <= 0 && $is_url_registration !== true) {
            if($popup_error === true)
                CLHF::SESSION_PushFeedback('info', 'Please register first.');
            $data[1] = Redirect::to(route('register'));
            goto point1;
        }

        $user_id = CLHF::AUTH_UserID();
        $user_exists = CLHF::AUTH_UserExists2($user_id);
        if($user_exists[0] === true && $user_exists[1] === true) {
            $user_data = CLHF::AUTH_UserData($user_id);
            $user_roles = array_column($user_data['roles'], 0);
            $redirect_to = CLHF::AUTH_Redirection($user_roles);
            $data[1] = $redirect_to;
        } else {
            Auth::logout();  // force logout ghost users
            $data[0] = true;
        }
        point1:
        return $data;
    }

    public static function AUTH_PageGuardAuth(Request $request, array $allowed_roles, $user_id, bool $popup_error=true) {
        /*
            - Redirects to login if guest
            - Shows forbidden error if not authorized OR invalid user
            - 3rd param: 
                $popup_error
                    ? Redirect::back()->withInput && alert_popup
                    : return error View

            Usage:

                // ACCESS CONTROL CONFIGURATION
                public static $authed_roles = [  
                    'GET'  => [2, ],
                    'POST' => [2, ],
                ];

                // PAGE GUARD
                $user_id = CLHF::AUTH_UserID();
                $guard = CLHF::AUTH_PageGuardAuth($request, CLHF::$authed_roles, $user_id, true);
                if($guard !== true) {
                    return $guard;
                }
                
        */

        $data = true;
        $user_exists = CLHF::AUTH_UserExists2($user_id);//dd($user_exists);

        if($user_exists[0] === true) {  // if user exists
            if($user_exists[1] === true) {  // if user is activated
                //dd($user_exists[2]);
                if($user_exists[2] === true) {  // if user is verified
                    // PASSED
                    if(url()->current() === route('verification.notice')) {
                        CLHF::SESSION_PushFeedback('success', 'Account is already verified.');
                        return Redirect::to(route('home.index'));
                    }
                } else {
                    // FAILED, FORCE VERIFY FIRST
                    if(url()->current() === route('verification.notice')) {
                        $data = true;
                        goto point1;
                    } else {
                        CLHF::SESSION_PushFeedback('info', 'Please verify your email first.');
                        return Redirect::to(route('verification.notice'));
                    }
                }
            } else {  // if user is deactivated
                if($user_exists[2] === true) {  // if user is verified
                    // FAILED, FORCED LOGOUT
                    AUTH::logout();
                    CLHF::SESSION_PushFeedback('error', 'Account is deactivated.');
                    return Redirect::to(route('login'));
                } else {  // if user is unverified
                    // FAILED, FORCE VERIFY FIRST
                    if(url()->current() === route('verification.notice')) {
                        $data = true;
                        goto point1;
                    } else {
                        CLHF::SESSION_PushFeedback('info', 'Please verify your email first.');
                        return Redirect::to(route('verification.notice'));
                    }
                }
            }
        } else {  // if guest
            CLHF::SESSION_PushFeedback('error', 'Please login first.');
            return Redirect::to(route('login'));
        }

        $err_msg = '';
        $allowed_roles2 = $allowed_roles[$request->method()] ?? [];
        $is_authorized = CLHF::AUTH_RoleAuthorized($allowed_roles2, $user_id);
        
        if($is_authorized !== true) {
            $err_msg = 'Unauthorized access.';
        }

        if(!empty($err_msg)) {
            //abort(403, 'Unauthorized action.');
            if($popup_error === true) {
                CLHF::SESSION_PushFeedback('error', $err_msg);
            }
            $data = CLHF::VIEW_ErrorResponse(403, 'Error', $err_msg, WebClient::nextURL());
        }

        point1:
        return $data;
    }

    public static function AUTH_RoleAuthorized($authorized_roles, $user_id) {
        /*
            @param $authorized_roles (bool || array)
            @param $authorized_roles = ['User', 1, 'Editor', 2, 3]  // (user_type string) OR (user_type ID) OR (mixture of both)
            @param $authorized_roles = true  // allow all
        */
        
        $is_authorized = false;
        
        // validate user
        $user_exists = CLHF::AUTH_UserExists2($user_id, true);  // checks also `is_valid=1`
        if($user_exists[0] !== true) goto point1;

        // validate $authorized_roles var_type
        $AR_types = ['boolean', 'array'];
        $AR_type = gettype($authorized_roles);
        if(!in_array($AR_type, $AR_types) || (is_bool($AR_type) && $authorized_roles!==true) || (is_array($AR_type) && empty($authorized_roles)))
            goto point1;
        
        // check for allow all
        if($authorized_roles === true) {
            $is_authorized = true;
            goto point1;
        }
        
        // get user role_ids
        $user_role_ids = [];
        $data1 = CLHF::DBO('user_type')->where(['user_id'=>$user_id])->get() ?? [];
        foreach($data1 as $key=>$val) {
            $user_role_ids[] = $val->user_role_id;
        }
        
        // unifying to role_ids
        $auth_role_ids = [];
        foreach($authorized_roles as $key=>$val) {
            if(is_int($val)) {
                if(!in_array($val, $auth_role_ids))
                    $auth_role_ids[] = $val;
                continue;
            }
            if(is_string($val)) {
                $val1 = trim($val);
                if(empty($val1)) // ignore empty string values
                    continue;
                // get ids of role strings. If not found, it will not be added
                $role_id = CLHF::DBO('user_roles')->where('role', $val)->first()->id ?? 0;
                if(!in_array($role_id, $authorized_roles, true)) // 3rd param = strict mode to exclude zeroes
                    $auth_role_ids[] = $role_id;
                continue;
            }
        }

        // check role_ids if exists and if its `is_valid=1`
        $official_auth_role_ids = [];
        foreach($auth_role_ids as $key=>$val) {
            $needle = ['id'=>$val,'is_valid'=>1];  // id exists and is_valid=1
            $db2 = CLHF::DBO('user_roles')->where($needle);
            if($db2->exists())
                $official_auth_role_ids[] = $val;
        }

        // if `user_role_id` is in `official_auth_role_ids`
        foreach($user_role_ids as $key=>$val) {
            if(in_array($val, $official_auth_role_ids)) {
                $is_authorized = true;
                goto point1;
            }
        }
        
        point1:
        return $is_authorized;
    }

    public static function AUTH_UserRoles(int $user_id) {
        // only returns valid roles
        // return [[id, role]+]

        $user_roles = [];
        $user_exists = CLHF::AUTH_UserExists2($user_id);
        if($user_exists[0] !== true)
            return $user_roles;
        
        $arr1 = CLHF::DB_LookUp('user_type', ['user_id'=>$user_id, 'is_valid'=>1], true);
        $user_role_ids = array_column($arr1, 'user_role_id');
        sort($user_role_ids);
        //$arr2 = AppFn::OBJECT_toArray(CLHF::DBO('user_roles')->whereIn('id', $user_role_ids)->where(['is_valid'=>1])->get());
        $arr2 = CLHF::DB_LookUp('user_roles', ['id'=>$user_role_ids, 'is_valid'=>1], true);
        foreach($arr2 as $key=>$val) {
            $user_roles[] = [$val['id'], $val['role']];
        }
        return $user_roles;
    }

    /**
     * Attempt User Login
     *
     * @param Request $request
     * @param boolean $bypass_password Uses email only to log in
     * @param boolean $force_login Replace existing logged in user
     * @param boolean $from_register_success Appends success pop-up in session
     * @return void [is_success, msg, redirect_data]
     * @uses Illuminate\Http\Request Request
     * @requires $request->post()
     */
    public static function AUTH_LoginAttempt(Request $request, bool $bypass_password=false, bool $force_login=false, bool $from_register_success=false) {
        /*
            ONLY ACCEPTS POST REQUEST
            be careful when overriding the 2nd param
        */
        
        $APP_DEV_MODE = AppFn::CONFIG_env('APP_DEV_MODE', false, 'boolean');  // Detailed Mode
        $inputs_old = [];
        $errors_first = [];
        $data = [false, '', null];
        try {
            // check if guest, otherwise throws error
            if(!is_null(Auth::id())) {
                if($force_login === true)
                    Auth::logout();
                else
                    throw new exception('Please logout first.');
            }
            
            // INPUT DATA ANALYSIS
            $subject['inputs_data']         = $request->post();
            $subject['validation_handler']  = [\App\Http\Controllers\Auth\LoginController::class, 'login_validation'];
            $subject['inputs_override']     = [
                // LOGIN
                '_timezone'              => [$request->post('_timezone'), ''],
                '_next_url'              => [$request->post('_next_url'), ''],
                '_purpose'               => [$request->post('_purpose'), ''],
                'email'                  => [$request->post('email'), ''],
                'password'               => [$request->post('password'), ''],
                'password_confirmation'  => [$request->post('password_confirmation'), ''],  // unnecessary but needed
            ];
            
            $subject['inputs_snz_except'] = ['_next_url', '_timezone', 'password', 'password_confirmation', ];
            $subject['vsk_gen_except'] = ['_next_url', '_timezone', 'facebook_id', 'password_confirmation', ];
            $subject['inputs_old_except'] = ['_next_url', '_timezone', 'password', 'password_confirmation', ];
            $subject['errors_except'] = [];
            $subject['force_success'] = ['password', 'password_confirmation', ];
            $_IAX = CLHF::FORM_AnalyzeInputData($subject);
            $inputs_old = $_IAX['inputs_old'];
            $errors_first = $_IAX['errors_first'];
            
            // CHECK PURPOSE
            $purpose = $request->post('_purpose') ?? '';
            if(!AppFn::STR_MD5Equals('user_login', $purpose)) {
                throw new exception('Invalid purpose.');
            }

            // CHECK EMAIL
            $email = $_IAX['inputs']['email'];
            if(array_key_exists('email', $errors_first)) {
                throw new exception($APP_DEV_MODE ? $errors_first['email'] : 'Invalid credentials.');
            }

            // GET USER ID
            $dbp1 = CLHF::DB_LookUp('users', ['email'=>$email], true);
            $user_id = $dbp1[0]['id'] ?? 0;
            $user_exists = CLHF::AUTH_UserExists2($user_id);
            if($user_exists[0] !== true)
                throw new exception('User doesn\'t exists.');

            /*// CHECK USER STATE
            $dbp2 = CLHF::DB_LookUp('user_state', ['user_id'=>$user_id], true);
            $is_active = (($dbp2[0]['is_active'] ?? 0) === 1);
            if($is_active !== true)
                throw new exception('Account is deactivated.');*/

            // LOGIN ATTEMPT
            if($bypass_password === true) {
                $obj1 = Auth::loginUsingId($user_id);
                if(is_null(Auth::id())) {
                    Auth::logout();  // for safety
                    throw new exception('ID login attempt failed.');
                }
            } else {
                // CHECK PASSWORD
                $password = $_IAX['inputs']['password'];
                if(array_key_exists('password', $errors_first)) {
                    throw new exception($APP_DEV_MODE ? $errors_first['password'] : 'Invalid credentials.');
                }

                // VALIDATE CREDENTIALS
                $validate = Auth::validate(['email' => $email, 'password' => $password]);
                if($validate !== true)
                    throw new exception('Invalid credentials.');

                // ATTEMPT CREDENTIALS
                $attempt = Auth::attempt(['email' => $email, 'password' => $password]);
                if($attempt !== true)
                    throw new exception('Login attempt failed.');
            }

            // issue WebClient Info
            /*$issueClientInfo = WebClient::issueClientUAInfo($request);
            if($issueClientInfo[0] !== true) {
                throw new exception('Failed to issue web client info.');
                //throw new exception($issueClientInfo[1]);
            }*/

            // get user data
            $user_data = CLHF::AUTH_UserData($user_id);
            if($user_data === null)
                throw new exception('Failed to fetch user data.');

            // success
            $next_url = WebClient::getPrevURL();
            $user_role_ids = array_column($user_data['roles'], 0);
            $data[0] = true;
            $data[1] = 'Successfully logged in.';
            $data[2] = CLHF::AUTH_Redirection($user_role_ids, $next_url, true);
            if($from_register_success === true)
                CLHF::SESSION_PushFeedback('success', 'Successfully registered');
            CLHF::SESSION_PushFeedback('success', $data[1]);

        } catch(\Exception $ex) {
            Auth::logout();  // fail-safe
            //$err_msg = $ex->getMessage().' | '.$ex->getLine();dd($err_msg);
            $err_msg = $ex->getMessage();
            $data[1] = $err_msg;
            $data[2] = Redirect::to(route('login'))->withInput($inputs_old)->withErrors($errors_first);
            CLHF::SESSION_PushFeedback('error', $err_msg);
            goto point1;
        }

        point1:
        //dd($data);
        return $data;
    }

    public static function AUTH_Redirection(array $user_role_ids, string $next_url="", bool $return_object=true) {
        // does not check is_valid
        // @param $return_object ? redirect_object : route_string
        
        # config
        $redirect_to_str = null;
        $default = RouteServiceProvider::HOME;
        $user_role_defaults = [  # role_id => URI
            1    => '/administrator',
            2    => '/student',
        ];

        $next_url = AppFn::STR_Sanitize($next_url, false, true);  // trim next_url

        // convert all to lower case
        foreach($user_role_defaults as $key=>$val) {
            $user_role_defaults[$key] = strtolower($val);
        }

        if(!empty($next_url)) {  // if user has accessed other URL before logging in
            $redirect_to_str = $next_url;
            goto point1;
        }

        $x_user_role_ids = count($user_role_ids);
        if($x_user_role_ids < 1 || $x_user_role_ids > 1) {  // if no / multiple roles, just default
            $redirect_to_str = $default;
        } else {                                            // if single role, specify
            $user_role_id = $user_role_ids[0][0] ?? '';
            if(array_key_exists($user_role_id, $user_role_defaults)) {  // if role_id matches
                $redirect_to_str = $user_role_defaults[$user_role_id];
            } else {                                                    // if no match, just default
                $redirect_to_str = $default;
            }
        }
        point1:
        $redirect_to = $return_object ? Redirect::to($redirect_to_str) : $redirect_to_str;
        return $redirect_to;
    }

    public static function AUTH_RoleExists($role, bool $is_valid=true, int $return_col=0) {
        // accepts int, string or array(int, string)
        // returns [is_success, role_id/s]
        // $return_col [0,1,2] => [id & role, id, role]

        $output = [false, []];
        $generate_where = function($rl) {
            return [(is_int($rl) ? 'id' : 'role')=>$rl];
        };
        $c1 = $c2 = 0;
        //$where = $is_valid ? ['is_valid'=>1] : [];
        $arr99 = ['id'=>[], 'role'=>[]];
        if(!in_array($return_col, [0,1,2]))
            throw new Exception('$return_col must only be 0, 1, or 2');
        if(!in_array(gettype($role), ['integer', 'string', 'array']))
            throw new Exception('$role must be int or string or array.');
        if(is_string($role) && AppFn::STR_IsBlankSpace($role))
            throw new Exception('`$role` must not be an empty string');
        if(is_array($role)) {
            $bool1 = (AppFn::ARRAY_depth($role) === 1 && AppFn::ARRAY_GetType($role) === 'sequential');
            if($bool1 !== true)
                throw new Exception('Array `$role` must be a sequential type');
            // evaluate value types
            foreach($role as $key=>$val) {
                if(!in_array(gettype($val), ['integer', 'string']))
                    throw new Exception('Index # '.$key.' must be int or string.');
                if(is_string($val) && AppFn::STR_IsBlankSpace($val))
                    throw new Exception('Index # '.$key.' must not be an empty string');
                $arr = $generate_where($val);
                $k = array_key_exists('id', $arr) ? 'id' : 'role';
                $arr99[$k][] = $arr[$k];
            }
        } else {
            $arr = $generate_where($role);
            $k = array_key_exists('id', $arr) ? 'id' : 'role';
            $arr99[$k][] = $arr[$k];
        }
        $arr99['id'] = array_unique($arr99['id']);
        $arr99['role'] = array_unique($arr99['role']);

        // convert to role to id
        $new_ids = [];
        //$new_roles = [];
        foreach($arr99['role'] as $key=>$val) {
            $lookup = CLHF::DB_LookUp('user_roles', array_merge(['role'=>$val], ($is_valid ? ['is_valid'=>1] : [])), true);
            if(!empty($lookup)) {
                //$new_roles[] = $val;
                $ids = array_unique(array_column($lookup, 'id'));
                foreach($ids as $key2=>$val2) {
                    $new_ids[] = $val2;
                }
            } else {
                $new_ids[] = null;
            }
        }
        
        // appending role_ids to [id]
        // count $c1
        $count_not_int = 0;
        $new_ids2 = [];
        foreach($new_ids as $key=>$val) {
            if(!is_int($val))
                $count_not_int++;
            else
                $new_ids2[] = $val;
        }
        $new_ids2 = array_unique($new_ids2);
        foreach($new_ids2 as $key=>$val) {
            $arr99['id'][] = $val;
        }
        $arr99['id'] = array_unique($arr99['id']);
        $c1 = count($arr99['id']) + $count_not_int;

        // counting $c2
        sort($arr99['id']);
        $data = [];
        foreach($arr99['id'] as $key=>$val) {
            $lookup = CLHF::DB_LookUp('user_roles', array_merge(['id'=>$val], ($is_valid ? ['is_valid'=>1] : [])), true)[0] ?? [];
            if(!empty($lookup)) {
                $c2++;
                $data[] = ['id'=>$lookup['id'], 'role'=>$lookup['role']];
            }
        }

        $output[0] = ($c1 === $c2 && count($data) === $c2);
        if($output[0] === true) {
            if($return_col === 1)
                $output[1] = array_column($data, 'id');
            else if($return_col === 2)
                $output[1] = array_column($data, 'role');
            else
                $output[1] = $data;
        }
        return $output;
    }

    public static function AUTH_IsAdmin($user_id=null) {
        $uid = !is_null($user_id) ? $user_id : CLHF::AUTH_UserID();
        return CLHF::AUTH_RoleAuthorized(['Administrator'], $uid);
    }
    

    # ----------------------------------------------------------
    # / AUTH
    # ----------------------------------------------------------

















    # ----------------------------------------------------------
    # \ DATABASE
    # ----------------------------------------------------------

    public static function DB_ConnectionCheck(string $connection_name, string $table='') {
        $output = [false, ''];
        try {  // fault finding
            
            // check empty string
            if(AppFn::STR_IsBlankSpace($connection_name) === true)
                throw new exception('Empty connection name.');
            
            // try PDO
            $pdo_passed = false;
            try {
                DB::connection($connection_name)->getPdo();
                $pdo_passed = true;
            } catch(\Exception $ex2) {}
            if(!$pdo_passed)
                throw new exception('Invalid connection `'.$connection_name.'`');
            //$db_name = DB::connection($connection_name)->getDatabaseName();//dd($db_name);

            // check table
            if(!AppFn::STR_IsBlankSpace($table)) {
                if(!Schema::connection($connection_name)->hasTable($table))
                    throw new exception('Invalid table `'.$table.'`');
            }

            $output[0] = true;

        } catch(\Exception $ex) {
            $output[1] = $ex->getMessage();
        }
        point1:
        return $output;
    }

    public static function DBOParam($conn_tbl) {
        // evaluate the param $conn_tbl into [conn, table, db_name]

        if(!in_array(gettype($conn_tbl), ['string', 'array']) || empty($conn_tbl))
            throw new exception('Invalid DBO param.');

        $bool1 = is_array($conn_tbl) && count($conn_tbl)===2;
        //$conn = ($bool1 ? $conn_tbl[0] : AppFn::CONFIG_env('APP_CONNECTION', '', 'string'));
        $conn = ($bool1 ? $conn_tbl[0] : AppFn::CONFIG_env('APP_CONNECTION', '', 'string'));
        $table = ($bool1 ? $conn_tbl[1] : (is_array($conn_tbl) ? $conn_tbl[1] : $conn_tbl));
        $db_name = '';
        try {
            $db_name = DB::connection($conn)->getDatabaseName();
        } catch(\Exception $ex) {}
        $ct = [$conn, $table, $db_name];
        return $ct;
    }

    public static function DBO($conn_tbl) {  // DataBase Object
        $dbop = CLHF::DBOParam($conn_tbl);//dd($dbop);
        $connection = $dbop[0];
        $table = $dbop[1];
        $conn_check = CLHF::DB_ConnectionCheck($connection, $table);
        if($conn_check[0] !== true)  // check connection
            throw new Exception($conn_check[1]);
        return DB::connection($connection)->table($table);
    }

    public static function DB_PreloadExists(array $el, array $db) {
        /*
        To successfully use this function, 
        your DB Preload Table must have at least the following fields:
            1) { desired_field_name } (varchar { desired_length })
            2) created_at (varchar 26)
            3) is_valid (tinyint 1)
        */

        $op = [false, ''];  // [is_valid, msg]

        $attribute = $el[0];
        $value = $el[1];

        $tbl_name = $db[0];
        $col_name = $db[1];
        $is_valid = $db[2];

        $where = [$col_name => $value];
        if($is_valid)
            $where['is_valid'] = 1;
        $db2 = CLHF::DB_LookUp($tbl_name, $where, true);
        if(empty($db2)) {
            $op[1] = 'Invalid data.';
            goto point1;
        }

        $op[0] = true;

        point1:
        return $op;
    }

    public static function DB_UsersAuthLinksTable(string $needle, bool $val_search=true) {
        // $val_search ? value search : key search
        $arr = [
            'facebook' => 'users_facebook',
            'google' => 'users_google',
        ];
        foreach($arr as $key=>$val) {
            // skip null key or val
            if(empty(trim($key)) || empty(trim($val)))
                continue;
            if($val_search === true) {
                if($key === $needle)
                    return $val;
            } else {
                if($val === $needle)
                    return $key;
            }
        }
        return null;
    }

    public static function DB_InsertOrFetchID($conn_tbl, array $needle, array $val) {
        // $needle => DB::raw
        // can return multiple IDs

        $data = null;
        DB::beginTransaction();
        try {
            $CONN = CLHF::DBO($conn_tbl);
            $arr1 = $CONN->where($needle);
            if($arr1->count() > 0) {
                $data = array_column(AppFn::OBJECT_toArray($arr1->get()), 'id');
                if(empty($data))
                    throw new exception('Fetched data was empty.');
            } else {
                $insID = $CONN->insertGetID(array_merge($needle, $val));
                if($insID === null)
                    throw new exception('Insert failed.');
                $data = $insID;
            }
            DB::commit();
        } catch(\Exception $ex) {
            DB::rollback();
            $err_msg = $ex->getMessage();
            dd($err_msg);
        }
        
        point1:
        return $data;
    }

    public static function DB_CacheFetSertID($conn_tbl, array $needle, bool $case_sensitive=true) {
        // DB Cache -> Fetch ID or InsertGetID
        // table prefix `ad_` -> Atomic Data
        // does not sanitize the value
        // doesn't care about `is_valid` since it's a caching approach
        // single value only per call
        // set 3rd param to true ONLY IF you're sure if its case-insensitive and there are no diacritics involved

        $ct = CLHF::DBOParam($conn_tbl);
        
        if(Schema::connection($ct[0])->hasTable($ct[1]) !== true)  // check table if exists
            throw new exception('Table `'.$ct[1].'` not found');
        if(Str::startsWith($ct[1], 'ad_') !== true)  // check table prefix
            throw new exception('`'.$ct[1].'` is not an atomic table');
            
        $allowed_types = ['string', 'integer', 'float', 'double', 'boolean'];
        $id = 0;
        $needle2 = [];
        unset($needle['created_at'], $needle['is_valid']);  // delete to avoid glitches

        $val_is_blank = true;
        $c_strings = 0;
        foreach($needle as $key=>$val) {
            if(!is_string($key) || (is_string($key) && AppFn::STR_IsBlankSpace($key)))
                throw new exception('Array key must be a filled string');
            if(!in_array(gettype($val), $allowed_types)) {
                throw new exception('Invalid value type');
            }
                
            
            if(is_string($val)) {
                $c_strings++;
                // strict mode for empty/blank space string
                if(!AppFn::STR_IsBlankSpace($val))
                    $val_is_blank = false;
                $needle2[$key] = $val;
            } else {
                $val_is_blank = false;
                $needle2[$key] = $val;
            }
        }

        // check if val is blank
        if($val_is_blank) {
            // if all needle has blank string, auto return 0 as ID
            if($c_strings === count($needle))  
                goto point1;
            else
                throw new exception('At least one element must have non-empty value');
        }

        $needle = $case_sensitive ? $needle : CLHF::DB_TransformRawLike($needle);
        $query_success = false;
        $data = [$ct, $needle, ['created_at'=>DT::now_str(), 'is_valid'=>1]];
        try {
            // $upsert = CLHF::DB_upsert($data[0], $data[1], $data[2]);
            // $query_success = $upsert[0];
            // $id_ = (int)($upsert[2][0]['id'] ?? null);
            // $id = !is_null($id_) ? $id_ : null;

            $lookup1 = CLHF::DB_LookUp($data[0], $data[1], true);
            if(!empty($lookup1)) {  // lookup
                $id = $lookup1[0]['id'] ?? null;
            } else {  // insert
                $data2 = array_merge($data[1], $data[2]);
                $id = DB::connection($data[0][0])->table($data[0][1])->insertGetId($data2);
            }
        } catch(\Exception $ex) {
            //dd($ex);
        }

        point1:
        if(is_null($id))
            throw new exception('Cache add failed');
            
        return $id;
    }

    public static function DB_LookUp($conn_tbl, array $where, bool $to_array=false, array $orderBy=[]) {
        /*
            @param $conn_tbl => (table_name or [connection_name, table_name])

            Please use the built-in function if the query is very complex:
                DB::connection(connection_name)->table(table_name)->chain_method()

            - use ($where=[true]) to show all records on a table
            - ($where=[]) will automatically return blank records
            - declaring the array value as sequential array will invoke whereIn()
                (e.g. ['id=?'=>[1,2,3]])
            - use '<raw>' at the start of array key to invoke whereRaw() method
                (e.g. ['<raw>id=? AND is_valid=?'=>[2, 1]])
        */

        $output = collect([]);

        // check where
        if(empty($where))
            goto point1;
        else if(count($where) === 1 && array_key_exists(0, $where) && $where[0] === true)
            $where = [];
        
        $dbp1 = CLHF::DBO($conn_tbl);

        $allowed_types = ['string', 'integer', 'float', 'double', 'boolean', 'null'];
        $raw_kw = '<raw>';
        foreach($where as $key=>$val) {
            if(is_string($key)) {  // if associative array
                $bool1 = (str_starts_with($key, $raw_kw) && !empty(substr($key, strlen($raw_kw))) && AppFn::ARRAY_GetType($val) === 'sequential');
                $bool2 = (is_array($val) && AppFn::ARRAY_GetType($val) === 'sequential');
                $bool3 = (in_array(strtolower(gettype($val)), $allowed_types));
                if($bool1 === true) {
                    // whereRaw()
                    $raw_prep = substr($key, strlen($raw_kw));
                    $dbp1 = $dbp1->whereRaw($raw_prep, $val);
                }
                else if($bool2 === true) {
                    // whereIn()
                    $dbp1 = $dbp1->whereIn($key, $val);
                }
                else if($bool3 === true) {
                    // where()
                    //$dbp1 = $dbp1->where($key, $val);
                    $dbp1 = $dbp1->whereRaw('BINARY `'.$key.'`=?', [$val]);
                }
            }
        }
        $dbp2 = $dbp1;
        foreach($orderBy as $key=>$val) {//dump($key);dd($val);
            //$dbp2 = $dbp2->orderBy($val[0], $val[1]);
            $dbp2 = $dbp2->orderBy($key, strtolower($val));
        }
        //$output = $dbp2->get() ?? collect([]); // fail-safe
        $output = $dbp2->get() ?? $output; // fail-safe
        point1:
        return $to_array ? (array)AppFn::OBJECT_toArray($output)['items'] : $output;
    }

    /*public static function DB_FetchPreload(string $tbl_name, array $order_by=[], bool $only_valid=true) {
        $db1 = DB::table($tbl_name);
        $db1 = $only_valid ? $db1->where(['is_valid'=>1]) : $db1;
        foreach($order_by as $key=>$val) {
            $db1 = $db1->orderBy($key, $val);  # ????
        }
        $output = $db1->get() ?? collect([]);
        return AppFn::OBJECT_toArray($output);
    }*/

    public static function DB_TransformRawLike(array $arr){
        $exclude = ['created_at', 'updated_at', 'is_valid', 'is_official'];
        $output = [];
        foreach($arr as $key=>$val) {
            if(is_string($val) && !in_array($key, $exclude)) {                
                $k = '<raw>LOWER('.$key.') LIKE ?';
                $v = [Str::of($val)->trim()->lower()->__toString()];
                $output[$k] = $v;
            } else {
                $output[$key] = $val;
            }
        }
        return $output;
    }

    public static function DB_PreloadFetch($conn_tbl, array $where, array $order_by=[], bool $case_sensitive=true) {
        // use this func to fetch preloads
        // ensure that where is associative array
        // the where key must corresponds to the table column
        // the where value trait must corresponds to the column info
        // you can specify `is_valid` or `is_official` in $where        
        // set 3rd param to true ONLY IF you're sure if its case-insensitive and there are no diacritics involved

        $ct = CLHF::DBOParam($conn_tbl);
        if(Schema::connection($ct[0])->hasTable($ct[1]) !== true)  // check table if exists
            throw new exception('Table `'.$ct[1].'` not found');
        if(Str::startsWith($ct[1], 'pl_') !== true)  // check table prefix
            throw new exception('`'.$ct[1].'` is not a preload table');

        $where2 = [];
        if(empty($where)) {
            $where2 = [true];
        } else {
            $where2 = $case_sensitive ? $where : CLHF::DB_TransformRawLike($where);
        }
        $lookup1 = CLHF::DB_LookUp($conn_tbl, $where2, true, $order_by);
        return $lookup1;
    }

    public static function DB_PreloadGetID($conn_tbl, array $where, bool $case_sensitive=true) {
        return CLHF::DB_PreloadFetch($conn_tbl, $where, [], $case_sensitive)[0]['id'] ?? 0;
    }

    public static function DB_PreloadAdd($conn_tbl, array $col_val, bool $is_official=false) {
        $output = [false, []];  // [is_success, data]
        // removing potential bug
        unset($col_val['created_at'], $col_val['is_official'], $col_val['is_valid']);
        $data = [
            $conn_tbl,
            $col_val,
            [
                'created_at' => DT::now_str(),
                'is_official' => ($is_official === true) ? 1 : 0,
                'is_valid' => 1,
            ],
        ];
        $upsert = CLHF::DB_upsert($data[0], $data[1], $data[2]);
        if($upsert[0] === true)
            $output = [$upsert[0], $upsert[2]];
        point1:
        return $output;
    }

    /*public static function DB_TransactionMockUp() {
        DB::beginTransaction();
        try {
            $rand = AppFn::STR_GenerateRandomAlphaNum(10);
            DB::table('__')->where(['id'=>1])->update(['mockup'=>$rand]);
            DB::commit();
        } catch(\Exception $ex) {
            DB::rollback();
        }
    }*/
    
    public static function DB_PreloadImportJSON(string $file_basename, $conn_tbl, array $col_val, bool $dropAndCreate=false) {

        /*
        Note:
            1) Table must exists
            2) The table must have at least these columns:
                id (AUTO INCREMENT), created_at(VARCHAR 26), is_official(TINYINT 1), is_valid(TINYINT 1)
        */

        $ct = CLHF::DBOParam($conn_tbl);
        //$CONN = CLHF::DBO($conn_tbl);
        $max_custom_cols = 5;
        if(AppFn::ARRAY_IsTypeSequential($col_val) === true)
            throw new exception('`$col_val` must be an associative array.');
        if(count($col_val) > $max_custom_cols)
            throw new exception('`$col_val` # of elements exceeded from '.$max_custom_cols);

        //$tbl_name = $tblname;
        //$jsonfilepath = public_path()."\assets\json\\".$file_basename.".json";
        $jsonfilepath = resource_path('_json/'.$file_basename.'.json');
        $json_str = file_get_contents($jsonfilepath);
        $json = json_decode($json_str, true);
        $insert = [];
        foreach($json as $key=>$val) {
            $value1 = is_string($val) ? [$val] : $val;
            $row = [];
            foreach($col_val as $key2=>$val2) {
                $row[$key2] = $value1[$val2];
            }
            //$row['seq'] = $key + 1;
            $row['created_at'] = DT::now_str();
            $row['is_official'] = 1;
            $row['is_valid'] = 1;
            $insert[] = $row;
        }

        // backup data
        $data = [];
        try {
            $data = AppFn::OBJECT_toArray(DB::connection($ct[0])->table($ct[1])->get());
        } catch(\Exception $ex) {}
        
        // drop and create
        if($dropAndCreate === true) {
            Schema::connection($ct[0])->dropIfExists($ct[1]);
            Schema::connection($ct[0])->create($ct[1], function(Blueprint $table) use($col_val) {
                $table->bigIncrements('id');
                foreach($col_val as $key=>$val) {
                    $table->string($key, 100);
                }
                $table->string('created_at', 26);
                $table->tinyInteger('is_official')->unsigned();
                $table->tinyInteger('is_valid')->unsigned();
            });
        }

        // reverse data if failed
        $dbp2 = DB::connection($ct[0])->table($ct[1])->insert($insert);
        if($dbp2 !== true && !empty($data)) {
            $dbp3 = DB::connection($ct[0])->table($ct[1])->insert($data);
            if(!empty($data) && $dbp3 !== true)
                throw new exception('Data rollback failed.');
            throw new exception('JSON insert failed to DB.');
        }
        return true;
    }

    public static function DB_ColInfo($conn_tbl, string $col_name) {
        $ct = CLHF::DBOParam($conn_tbl);
        $db1 = DB::connection($ct[0])->select(DB::raw("SHOW FIELDS FROM ".$ct[1]));
        $arr = [];
        foreach($db1 as $key=>$val) {
            $arr2 = [];
            foreach($val as $key2=>$val2) {
                $arr2[$key2] = $val2;
            }
            if(!empty($arr2)) {
                $arr[] = $arr2;
            }
        }
        $type = '';
        foreach($arr as $key=>$val) {
            if($val['Field'] == $col_name) {
                $type = $val['Type'];
                break;
            }
        }
        $pcs1 = [];
        if(!empty($type)) {
            $pcs1 = explode('(', $type);
            $pcs1[1] = (int) rtrim($pcs1[1], ')');
        }
        if(empty($pcs1)) {
            throw new exception('Field type and length/value not found.');
        }
        return $pcs1;
    }


	

    public static function DB_select(string $conn, string $sql, array $binding=[], bool $to_array=false) {
        //$conn = AppFn::STR_IsBlankSpace($conn) ? AppFn::CONFIG_env('APP_CONNECTION', '', 'string') : $conn;
        $select = DB::connection($conn)->select($sql, $binding);
        $output = $to_array ? AppFn::OBJECT_toArray($select) : $select;
        return $output;
    }

    public static function DB_select_arr(string $conn, string $sql, array $binding=[], $key=[]) {
        $arr1 = CLHF::DB_select($conn, $sql, $binding, true);
        return is_array($key) ? $arr1 : ($arr1[$key]);
    }

    public static function DB_insert($conn_tbl, array $values, array $where=[], bool $strict=false) {

        /*
            @param $strict = true ? no inserting if already exists : insert always
        */

        if(empty($values))
            throw new exception('$values must not be empty');
        if(AppFn::ARRAY_depth($values) > 2)
            throw new exception('$values must not exceed 2 depths');
            
        $values = (AppFn::ARRAY_depth($values) === 1) ? [$values] : $values;

        $CONN = CLHF::DBO($conn_tbl);
        $CONN->lockForUpdate();
        $output = [false, 0, []];

        $insert = $CONN->insert($values);
        if($insert !== true)
            goto point1;

        $data = [];
        foreach($values as $key=>$val) {
            $lookup = CLHF::DB_LookUp($conn_tbl, $val, true)[0] ?? [];
            $data[] = $lookup;
        }
        $output = [true, count($values), $data];

        point1:
        return $output;
    }

    public static function DB_upsert($conn_tbl, array $attr, array $values, array $lu=[]) {
        // supports tables that has no AUTO_INCREMENT id
		// the invocation is irreversible
        // specify $lu if $attr or $values has a value of null (and do not specify null value in $lu)
		// returns [success, affected_rows (int), lookup_data]

        $CONN = CLHF::DBO($conn_tbl);
        $data = [false, 0, []];

		$affected_rows = 0;
		$merged = array_merge($attr, $values);
        $lookup = null;

        // start
        $arr1 = CLHF::DB_LookUp($conn_tbl, $merged, true);
        $arr2 = CLHF::DB_LookUp($conn_tbl, $attr, true);

		// find existing results
		$arr1_count = count($arr1);
        if($arr1_count > 0) {
            $affected_rows = $arr1_count;
            //dd($affected_rows);
			goto point1;
        }

		// find same attr but different values
		$arr2_count = count($arr2);
		if($arr2_count > 0) {
			$dbp2 = $CONN->where($attr)->update($values);
            $dbp2_count = count(CLHF::DB_LookUp($conn_tbl, $merged, true));
            $affected_rows = $dbp2_count;
			goto point1;
		}

		// insert attr and values
		$insert_id = false;
        try {
            $insert_id = $CONN->insertGetID($merged);
        } catch(\Exception $ex) {
            //dd($ex->getMessage());
        }
        $dbp3 = (is_int($insert_id) && $insert_id >= 0);
        if($dbp3 === true) {
            //$dbp4 = CLHF::DB_LookUp($conn_tbl, ['id'=>$insert_id], true);
            $dbp4 = CLHF::DB_LookUp($conn_tbl, $merged, true);
			$dbp4_count = count($dbp4);
            $affected_rows = $dbp4_count;
            if($affected_rows > 0) {
                $lookup = $dbp4;
            }
            else if(!empty($lu)) {
                $dbp5 = CLHF::DB_LookUp($conn_tbl, $lu, true);
                $dbp5_count = count($dbp5);
                $affected_rows = $dbp5_count;
                if($dbp5_count > 0) {
                    $lookup = $dbp5;
                }
            }
		}

		point1:
        $lookup = !is_null($lookup) ? $lookup : CLHF::DB_LookUp($conn_tbl, $merged, true);
        $data[0] = ($affected_rows > 0 && count($lookup) > 0);
        $data[1] = $affected_rows;
        $data[2] = $lookup;
        return $data;
	}

    public static function DB_delete($conn_tbl, array $where) {
        $ct = CLHF::DBOParam($conn_tbl);
        $is_success = false;
        $lu1 = CLHF::DB_LookUp($ct, $where, true);
        $count1 = count($lu1);
        $dbp1 = CLHF::DBO($ct)->where($where)->delete();//dd($dbp1);
        if($dbp1 >= 0) {
            $lu2 = CLHF::DB_LookUp($ct, $where, true);
            $count2 = count($lu2);
            $is_success = ($count1 <= 0) ? true : ($count2 === 0);
        }
        return $is_success;
    }

    public static function DB_sql_parse(string $file_basename, string $connection, array $params=[], bool $assoc_array=false) {
        // only for select query
        // do not include .sql
        //$sql_path = app_path().'\\SQL\\'.$file_basename.'.sql';
        $sql_path = resource_path('_sql/'.$file_basename.'.sql');
        $sql = file_get_contents($sql_path);//dd(DB::connection($connection));
        $data = DB::connection($connection)->select($sql, $params);//dd($data);
        $arr = $assoc_array ? AppFn::OBJECT_toArray($data) : $data;
        return $arr;
    }

    public static function DB_stored_procedure(string $conn, string $func_name, array $binding=[], bool $to_array=false) {
        $FR_app = FieldRules::getGeneral();
        $kw = 'CALL ';  // keyword starts with, with 1 space
        $func_name = AppFn::STR_Sanitize($func_name);
        if(AppFn::STR_preg_match($FR_app['function']['regex'], $func_name) !== true)
            throw new exception('`$func_name` must be a valid function name');
        $param = '';
        $x = -1;
        foreach($binding as $key=>$val) {
            $param .= ((++$x)>0 ? ',' : '').'?';
        }
        $sql = $kw.$func_name.'('.$param.')';//dd($param);
        $select = DB::connection($conn)->select($sql, $binding);
        $output = $to_array ? AppFn::OBJECT_toArray($select) : $select;
        return $output;
    }

    public static function DB_stored_function(string $conn, string $func_name, array $binding=[], bool $to_array=false) {
        $FR_app = FieldRules::getGeneral();
        $kw = 'EXEC ';  // keyword starts with, with 1 space
        $func_name = AppFn::STR_Sanitize($func_name);
        if(AppFn::STR_preg_match($FR_app['function']['regex'], $func_name) !== true)
            throw new exception('`$func_name` must be a valid function name');
        $param = '';
        $x = -1;
        foreach($binding as $key=>$val) {
            $param .= ((++$x)>0 ? ',' : '').'?';
        }
        $sql = $kw.$func_name.'('.$param.')';//dd($param);
        $select = DB::connection($conn)->select($sql, $binding);
        $output = $to_array ? AppFn::OBJECT_toArray($select) : $select;
        return $output;
    }

    public static function DB_table_column_all($conn_tbl) {
        /*
            gets table columns
        */
        $ct = CLHF::DBOParam($conn_tbl);
        $cols = Schema::connection($ct[0])->getColumnListing($ct[1]);
        return $cols;
    }

    public static function DB_table_column_info($conn_tbl, array $column=[]) {
        /*
            gets table info of columns or specific column
            @param $column = (string, string[])
        */

        $ct = CLHF::DBOParam($conn_tbl);//dd($ct);
        //$dbo = CLHF::DBO($conn_tbl);  // check connection
        $table_cols = CLHF::DB_table_column_all($ct[1]);
        $cols = !empty($column) ? $column : $table_cols;
        
        // FAULT FINDING
        if(AppFn::ARRAY_GetType($cols) !== 'sequential')  // check if sequential
            throw new exception('`$column` must be sequential array');
        foreach($cols as $key=>$val) {  // check array value
            if(!is_string($val))
                throw new exception('Array index '.$key.' must be string');
            if(AppFn::STR_IsBlankSpace($val))
                throw new exception('Array index '.$key.' is empty');
            if(!in_array($val, $table_cols))
                throw new exception('Column `'.$ct[2].'`.`'.$val.'` not found');
        }

        $output = [];
        foreach($cols as $key=>$val) {
            $obj = DB::connection($ct[0])->getDoctrineColumn($ct[1], $val);
            $output[$val] = AppFn::OBJECT_reflect($obj, true);
        }
        return $output;
    }

    public static function DB_table_column_gap($conn_tbl, string $col, bool $get_first=false) {
        /*
            make sure to lock first the table and enclose it in a transaction
        */
        $types_int = [
            'Doctrine\DBAL\Types\SmallIntType',
            'Doctrine\DBAL\Types\IntegerType',
            'Doctrine\DBAL\Types\BigIntType',
        ];
        $ct = CLHF::DBOParam($conn_tbl);
        $col_info = CLHF::DB_table_column_info($ct, [$col])[$col] ?? [];

        // check col type
        $col_type = get_class((object)($col_info['_type'] ?? []));
        if(!in_array($col_type, $types_int))
            throw new exception('Column `'.$ct[2].'`.`'.$col.'` must be an integer type');

        $sql = "
            SELECT
                z.expected as start,
                CAST(IF(z.got-1>z.expected, z.got-1, z.got-1) AS UNSIGNED) as end
            FROM (
                SELECT
                    @rownum:=@rownum+1 AS expected,
                    IF(@rownum={COL}, 0, @rownum:={COL}) AS got
                FROM
                    (SELECT @rownum:=0) AS a
                JOIN ".$ct[1]."
                ORDER BY {COL}
            ) AS z
            WHERE z.got!=0
        ";
        $sql2 = str_replace('{COL}', $col, $sql);
        $select = CLHF::DB_select_arr($ct[0], $sql2, []);
        $output = $get_first ? ($select[0]['start'] ?? null) : $select;
		return $output;
	}
    
    public static function DB_table_column_value_entwined($conn_tbl, array $values, array $except_cols=[]) {
        /**
         * @param $conn_tbl Connection name and table
         * @param $values Array key and value
         * @param $except_cols Columns to except
         * @return bool
         */

		// does not validate length
        $ct = CLHF::DBOParam($conn_tbl);
        $db1 = CLHF::DB_select($ct[0], "SHOW FIELDS FROM ".$ct[1], [], true);
		$tbl_info = AppFn::OBJECT_toArray($db1);
        $is_valid = false;
		$col_types = [
			'string' => ['varchar', 'text'],
			'integer' => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'],
		];
        try {
            // forming col info => type & size
            $cols = [];
            foreach($tbl_info as $key=>$val) {
                $field = $val['Field'];
                //if(in_array($field, $except_cols))
                //    continue;
                $type = $val['Type'];
                $pcs1 = [];
                if(AppFn::STR_preg_match('/^[a-zA-Z]+((\([0-9]+\)){1}( [a-zA-Z]+)?)?$/u', $type)) {
                    $pcs1_1 = explode('(', $type);
                    $pcs1_2 = explode(')', $type);

                    $pcs1[0] = $pcs1_1[0];
                    $pcs1[1] = count($pcs1_1)>1 ? (int)rtrim($pcs1_1[1], ')') : null;
                    $pcs1[2] = AppFn::STR_Sanitize($pcs1_2[1] ?? '');
                } else {
                    throw new exception('Column Field or Type (length/value) not found.');
                }
                $cols[$field] = $pcs1;
            }

            // removing excepts in cols
            $cols2 = [];
            foreach($cols as $key1=>$val1) {
                if(!in_array($key1, $except_cols))
                    $cols2[$key1] = $val1;
            }

            // removing excepts in values
            $values2 = [];
            foreach($values as $key1=>$val1) {
                if(!in_array($key1, $except_cols))
                    $values2[$key1] = $val1;
            }

            // detecting missing VALUES
            foreach($cols2 as $key1=>$val1) {
                if(!array_key_exists($key1, $values2)) {
                    throw new exception('Missing array key `'.$key1.'`');
                }
            }
            
            // validating VALUES
            foreach($values2 as $key1=>$val1) {
                if(array_key_exists($key1, $cols2)) {
                    $type = $cols2[$key1][0];
                    //$type = $cols2[$key1] ?? '';
                    $gettype = '';
                    foreach($col_types as $key2=>$val2) {
                        if(in_array($type, $val2)) {
                            $gettype = $key2;
                            break;
                        }
                    }
                    if(empty($gettype))
                        throw new exception('Type not found for array key `'.$key1.'`');
                    if(strtolower(gettype($val1)) !== $gettype)
                        throw new exception('Type mismatch for `'.$key1.'` (assumes '.$gettype.')');
                } else {
                    throw new exception('`'.$key1.'` column field doesn\'t exists.');
                }
            }
            $is_valid = true;
        } catch(\Exception $ex) {
            $err_msg = $ex->getMessage().'|'.$ex->getLine();
            //dd($err_msg);
            $is_valid = false;
        }
        return $is_valid;
    }

    # ----------------------------------------------------------
    # / DATABASE
    # ----------------------------------------------------------






















    # ----------------------------------------------------------
    # \ FORM
    # ----------------------------------------------------------

    public static function FORM_AnalyzeInputData($subject) {
        
        $validation_handler        = $subject['validation_handler'];
        $inputs_data               = $subject['inputs_data'];
        $inputs_override           = $subject['inputs_override'];
        $inputs_snz_except         = $subject['inputs_snz_except'];
        $inputs_old_except         = $subject['inputs_old_except'];
        $vsk_except                = $subject['vsk_gen_except'];
        $errors_except             = $subject['errors_except'];
        $force_success             = $subject['force_success'];
        
        # ---------------------------------
        # :: -- ANALYSIS PROCESS -- ::
        # 1) SANITIZE INPUT DATA
        # 2) VALIDATE INPUT DATA
        # 3) PULL FIRST ERRORS ONLY
        # 4) GENERATE VSK BASED ON VALIDATION STATE
        # 5) MERGE NEW_VSK to INPUT DATA
        # 6) REVERSE OVERRIDE OF OLD INPUT DATA
        # ---------------------------------

        /*
            $inputs_data         = $request->post();

            $validation_handler   = [CLHF::class, 'FUNCTION_NAME'];

            $inputs_override     = [
                OVERRIDE or ADD SOMETHING ON REQUEST DATA (WHAT DATA TO EXPECT)
                RESPECTIVE FALLBACK VALUES MUST BE IN CORRECT EMPTY DATA TYPE
                TO PREVENT LOGIC GLITCHES
                3rd param (bool) if true => use the value of inputs_data, else input_override;
                (e.g.)   string(''), array([]), int(0), double(0.0), bool(false|true)
            ];
            
            $inputs_reverse_override = [
                >> REVERSION OF OVERRIDEN INPUTS <<
                Provide the accurate attribute name and its fallback default value
                Fallback value must be in correct empty data type
                Specify only if there is an attribute that is affected by value manipulations (prefixing, suffixing, or replacement)
                    (original value)
                        mobilenumber => mobilenumber_value
                    (value manipulations)
                        mobilenumber => (prefix + mobilenumber_value)
                    (reversion)
                        mobilenumber => mobilenumber_value
                USAGE:
                    attrib => default_val
            ];

            $inputs_snz_except = [];    // Do not include Input Sanitation

            $vsk_gen_except = [];       // Do not include VSK Generation

            $errors_except = [];        // Do not include errors
            
            $force_success = [];        // Force success input key
        */
        
        
        # Declare empty VSK to prevent hijacking from client-side
        $APP_VSK_NAME = AppFn::CONFIG_env('APP_VSK_NAME', '', 'string');
        if(!is_string($APP_VSK_NAME) || AppFn::STR_IsBlankSpace($APP_VSK_NAME))
            throw new Exception('VSK name not found');
        $inputs_override[$APP_VSK_NAME] = [];

        # REFINE 3RD PARAM OF $inputs_override (bool)
        foreach($inputs_override as $key=>$val) {
            $bool = (array_key_exists(2, $inputs_override[$key]) && $inputs_override[$key][2]===true);
            $inputs_override[$key][2] = $bool;
        }
        
        # UPSERT _token & _purpose key
        $inputs_override['_token'] = [($inputs_data['_token'] ?? ''), '', false];
        $inputs_override['_purpose'] = [($inputs_data['_purpose'] ?? ''), '', false];
        
        # OVERRIDE INPUTS BASED FROM DEFAULT OR FALLBACK
        $inputs_override2 = [];
        foreach($inputs_override as $key=>$val) {
            if($key !== $APP_VSK_NAME) {
                $inputs_override2[$key] = (!is_null($val[0]) ? $val[0] : $val[1]);
            }
        }
        
        $VC           = new $validation_handler[0]();  // Dynamic Validator Class
        $inputs_old   = CLHF::FORM_SanitizeData($inputs_data);
        $inputs_data  = array_merge($inputs_data, $inputs_override2);
        $inputs_snz   = CLHF::FORM_SanitizeData($inputs_data, $inputs_snz_except);
        $validator    = $VC->{$validation_handler[1]}($inputs_snz);  // CLHF::VALIDATOR_SIS($inputs_snz);
        $errors       = CLHF::FORM_RefineErrors($validator->errors(), $errors_except);
        $errors_first = CLHF::VALIDATOR_FirstErrors($errors);
        $has_error    = (count($errors_first) > 0);
        $vsk          = CLHF::VALIDATOR_VSKGenerate($inputs_snz, $errors_first, $vsk_except);
        //$inputs_vsk   = AppFn::ARRAY_merge_ksort($inputs_snz, [AppFn::CONFIG_env('APP_VSK_NAME', '', 'string')=>$vsk]);
        
        // REFINE INPUTS OLD (ORIGINAL OR NEW VALUE)
        foreach($inputs_snz as $key=>$val) {
            if(!array_key_exists($key, $inputs_old))
                $inputs_old[$key] = '';
            $inputs_old[$key] = $inputs_override[$key][2]===true ? $inputs_old[$key] : $val;
        }
        ksort($inputs_old);
        
        // REMOVING INPUT OLD EXCEPTIONS
        $inputs_old2   = [];
        foreach($inputs_old as $key=>$val) {
            if(!in_array($key, $inputs_old_except)) {
                $inputs_old2[$key] = $val;
            }
        }
        
        // INSERT VSK TO OLD INPUTS
        $inputs_old3   = AppFn::ARRAY_merge_ksort($inputs_old2, [AppFn::CONFIG_env('APP_VSK_NAME', '', 'string')=>$vsk]);
        
        $data = $data_bk = [
            'inputs'         => $inputs_snz,
            'inputs_old'     => $inputs_old3,
            'errors_all'     => $errors,
            'errors_first'   => $errors_first,
            'vsk'            => $vsk,
            'validator'      => $validator,
        ];
        
        // FORCE CONVERT TO SUCCESS
        foreach($force_success as $key=>$val) {
            unset($data['errors_all'][$val]);
            unset($data['errors_first'][$val]);
            if(array_key_exists($val, $data['vsk'])) {
                $data['vsk'][$val] = 'success';
            }
            if(array_key_exists($val, $data['inputs_old'][AppFn::CONFIG_env('APP_VSK_NAME', '', 'string')])) {
                $data['inputs_old'][AppFn::CONFIG_env('APP_VSK_NAME', '', 'string')][$val] = $data['vsk'][$val];
            }
        }
        
        //$validator->validate();  // redirect back
        return $data;
    }

    

    public static function FORM_RefineErrors(object $validation_errors, array $except_errors=[]) {
        $errors_arr = AppFn::OBJECT_toArray($validation_errors);
        ksort($errors_arr);
        ksort($except_errors);
        
        // get except wildcards
        $except_wildcards = [];
        $except_singles = [];
        foreach($except_errors as $key=>$val) {
            if(!empty(trim($val))) {
                if(str_ends_with($val, '*')) {
                    $except_wildcards[] = substr($val, 0, -1);
                } else {
                    $except_singles[] = $val;
                }
            }
        }

        // append only that has no matches to wildcards and singles
        $refined_errors = [];
        foreach($errors_arr as $key=>$val) {
            $error_key = substr($key, 0, -1);
            $bool1 = (!in_array($error_key, $except_wildcards, true) && !in_array($key, $except_singles, true));
            if($bool1 === true) {
                $refined_errors[$key] = $val;
            }
        }
 
        //dd($refined_errors);
        return $refined_errors;
    }

    public static function FORM_SanitizeDataTree($data=[], $except=[]) {
        /**
         * be careful when putting suffix `_id` in array key, it will force the type to be integer
         * str_ends_with($key, '_id') ? 0 : ''
         * does not sanitize integer types
         */

        $new_data = [];
        foreach($data as $key => $val) {
            $k = is_string($key) ? AppFn::STR_Sanitize($key) : $key;
            $bool1 = (is_int($k) || (is_string($k) && !empty($k)));
            if(!$bool1)  // skip non-string or empty keys
                continue;
            if(is_array($val)) {
                unset($data[$key]);
                $new_data[$k] = CLHF::FORM_SanitizeDataTree($val, $except);  // 2nd param untested (fix this by adding feature level1.level2) ($except reduction)
            }
            else {
                $has_id_suffix = str_ends_with($key, '_id');
                $new_val = null;
                if(is_null($val)) {
                    $new_val = $has_id_suffix ? (int)$val : (string)$val;
                }
                else if(is_int($val) || $has_id_suffix === true) {
                    $new_val = (int)($val > 0 ? $val : 0);
                }
                else if(is_string($val)) {
                    $new_val = (!in_array($k, $except) ? AppFn::STR_Sanitize($val, true, true) : $val);
                }
                /*$new_val = (is_string($val))
                    ? (!in_array($k, $except) ? AppFn::STR_Sanitize($val, true, true) : $val)
                    : ''
                ;*/

                $new_data[$k] = $new_val;
                unset($data[$key]);
            }
        }
        return $new_data;
    }

    public static function FORM_SanitizeData(array $inputs1, array $except=[], bool $ksort_asc=true) {
        // == BASIC SANITIZATION ==
        // convert null values to empty string
        // trims leading & trailing whitespaces of keys and values
        // arrange array keys alpabetically

        $test_case = [
            'hello' => [
                null,
                'sad' => null,
                'asda' => [
                    'asddd' => null,
                    's' => '      hello       ',
                ],
            ],
            null,
            'mnnb' => [
                'q' => 's',
                'ssda' => [
                    't' => null,
                    'll' => [
                        'gs' => null,
                        's' => 'hello',
                        '   s' => [],
                        'qqq' => '     asdsa  asd as    asdas d    z    ',
                    ],
                ],
            ],
        ];

        $inputs2 = CLHF::FORM_SanitizeDataTree($inputs1, $except);
        $inputs3 = $inputs2;
        if($ksort_asc)
            ksort($inputs3);

        return $inputs3;
    }

    public static function FORM_LogicResponse(Request $request, $response) {
        // $response => [[status=>msg], [attr=>error_msg], response_obj]
        /*
            returns
                ajax: [[status=>msg], [attr=>error_msg]]
                get:  response_obj->withInput()->withError()->withFeedback()

            RESERVED KEYS:
                _messages, _errors, _data, _redirect_to
        */
        $response_ = $response;
        $response[3] = $response[3] ?? [];
        $is_redirect_obj = is_object($response[2]) && get_class($response[2]) === 'Illuminate\Http\RedirectResponse';
        $target_url = $is_redirect_obj ? (string)$response[2]->getTargetUrl() : '';

        // setting dummy error attribute, if there's error data or msg
        $has_error = false;
        foreach($response[0] as $key=>$val) {
            if($val[0] === 'error') {
                $has_error = true;
                break;
            }
        }
        if(array_key_exists('__', $response[1])) {
            unset($response[1]['__']);
        }
        if($has_error === true && empty($response[1])) {
            $response[1]['__'] = '';
        }
        if($request->ajax() || in_array($request->route()->getActionMethod(), [
            'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        ])) {
            $json = [
                '_messages' => $response[0],
                '_errors' => $response[1],
                '_data' => $response[3],
                '_redirect_to' => $has_error ? '' : $target_url,  // empty redirect url if there's error and if ajax
            ];
            return json_encode($json);
        } else {
            foreach($response[0] as $key=>$val) {
                CLHF::SESSION_PushFeedback($val[0], $val[1]);
            }
            if($is_redirect_obj === true) {
                return $response[2]->withError($response[1])->with($response[3]);
            } else {
                return $response[2];
            }
        }
    }

    // public static function FORM_LogicResponse2(Request $request, $response) {
    public static function FORM_LogicResponse2(Request $request, bool $is_success, array $messages, array $errors, array $redirect, array $data=[]) {
        /*
            $response = [
                bool_is_success,     // is_success
                [msg_status=>msg+],  // messages
                [attr=>error_msg+],  // errors
                [
                    0 => bool,       // is_forced
                    1 => string,     // redirect_route
                ],
                [],                  // data
            ]

            returns
                ajax: [[status=>msg], [attr=>error_msg]]
                load:  response_obj->withInput()->withError()->withFeedback()

            RESERVED KEYS:
                s, m, e, r, d
        */

        // $response_ = $response;
        $is_redirect_obj = is_object($redirect[1]) && get_class($redirect[1]) === 'Illuminate\Http\RedirectResponse';
        $target_url = $is_redirect_obj ? (string)$redirect[1]->getTargetUrl() : '';
        
        // data
        $data = $response[4] ?? [];
        $has_data = !empty($data);

        // validate message key (warning, error, success, info, & question)
        $has_messages = !empty($messages);
        $message_types = ['warning', 'error', 'success', 'info', 'question'];
        $x = -1;
        foreach($messages as $key=>$val) {
            $x++;
            if(!in_array($key, $message_types, true))
                throw new exception('Invalid message key `'.$key.'` at index '.$x);
        }

        // validate error elements
        $has_error = !empty($errors);
        $x = -1;
        foreach($errors as $key=>$val) {
            $x++;
            if(!is_string($key) || empty($key))
                throw new exception('Invalid error key `'.$key.'` at index '.$x);
            if(!is_string($val) || empty($val))
                throw new exception('Invalid error value `'.$val.'` at index '.$x);
        }

        // validate redirect url
        $redirect = [(bool)$redirect[0], $target_url, false];
        $is_valid_url = filter_var($redirect[1], FILTER_VALIDATE_URL);
        $redirect[2] = $is_valid_url;  // is valid url
        if(($redirect[0] || !empty($target_url)) && !$is_valid_url)
            throw new Exception('Invalid redirect url');
        
        // return
        $return = [
            's' => $is_success,   // is _success
            'm' => $messages,     // messages
            'e' => $errors,       // errors
            'r' => $redirect,     // redirect url
            'd' => $data,         // data
        ];
        // dd($return);

        // logic
        if($request->ajax()) {
            return json_encode($return);

        } else {
            // foreach($response[1] as $key=>$val) {
            //     CLHF::SESSION_PushFeedback($val[0], $val[1]);
            // }
            if(!$redirect[0] || !$redirect[2])
                throw new exception('No redirect url');
            
            $return2 = redirect()->to($redirect[1]);
            $return2->with('is_success', $is_success);
            if($has_data) $return2->withData($data);
            if($has_messages) $return2->with(['page'=>['alerts_popup'=>$messages]]);
            if($has_error) $return2->withErrors($errors);  // $return2->withError($errors)
            
            return $return2;
        }
    }

    # ----------------------------------------------------------
    # / FORM
    # ----------------------------------------------------------














    public static function STORAGE_DiskInfo(string $disk_name="", bool $createIfNotExists=false, $chmod_dir=null) {
        /*
            if empty, show all
            if specific disk, create and update mode
        */

        if(!(is_null($chmod_dir) || is_int($chmod_dir)))
            throw new exception('Invalid directory permission');
        
        $chmod_def = 0755;
        $chmod = !is_null($chmod_dir) ? $chmod_dir : $chmod_def;
        
        $disks = config('filesystems.disks');
        if(AppFn::STR_IsBlankSpace($disk_name) === true) {
            return $disks ?? [];
        } else {
            // check disk
            if(array_key_exists($disk_name, $disks) !== true)
                throw new Exception('Disk not found');

            $file_dir = AppFn::STR_Sanitize($disks[$disk_name]['root']);

            // check disk directory value
            if(AppFn::STR_IsBlankSpace($file_dir) === true)
                throw new exception('Disk directory value is empty');   
                
            // check if disk directory exists
            if(File::exists($file_dir) !== true && $createIfNotExists !== true)
                throw new Exception('Disk directory not found');
            
            // create directory if not exists, (if $createIfNotExists === true)
            if($createIfNotExists === true) {
                $tries = 0;
                $max_tries = 5;
                while(File::exists($file_dir) !== true) {
                    $tries++;
                    if($tries > $max_tries)
                        throw new exception('Failed to create disk directory');
                    $make_folder = File::makeDirectory($file_dir, $chmod, false, false);
                    if($make_folder === true)
                        break;
                }
            }

            return $disks[$disk_name];
        }
    }

    public static function STORAGE_FileURL(string $path, string $mode) {
        // explode segments

        // SETTINGS
        $url_len_max = 2000;
        $modes = ['dispose', 'download'];

        // check mode
        if(!in_array($mode, $modes))
            throw new exception('Invalid mode');
        $m = array_search($mode, $modes) + 1;

        // check if path exists
        $file = CLHF::STORAGE_FileInfo($path, true);

        if($file['exists'] !== true) {
            return '';
            // throw new exception('File doesn\'t exists');
        }

        $url = route('file.index', ['p'=> encrypt($file['path_app']), 'm'=> $m,]);
        $url_len = strlen($url);
        if($url_len > $url_len_max)
            throw new exception('File link has exceeded '.$url_len_max.' '.Str::plural('character', $url_len_max));
        
        return $url;
    }

    public static function STORAGE_FileUpload(Request $request, string $file_input, string $disk_name, array $file_name_ext, array $valid_extensions, bool $deleteExistingFile=false, \Closure $callback_before=null) {
        // file upload function with delete protection
        /*
            usage for $callback_before and $callback_after:
                $func = function($file_path) {                    
                    $err_msg = '';  // set some value to return an error
                    return $err_msg;
                }
        */

        $output = [false, ''];  // [is_success, msg_error, file_info]
        try {
            // check $valid_extensions
            if(empty($valid_extensions))
                throw new exception('Valid extensions is empty');

            // check $file_name_ext
            $bool = (
                count($file_name_ext) === 2
                && is_string($file_name_ext[0]) && !AppFn::STR_IsBlankSpace($file_name_ext[0])
                && is_string($file_name_ext[1]) && !AppFn::STR_IsBlankSpace($file_name_ext[1])
            );
            if($bool !== true)
                throw new Exception('Invalid structure (file_name_ext)');
            $file_basename = $file_name_ext[0];
            $file_ext = $file_name_ext[1];
            $file_name = $file_basename.(AppFn::STR_IsBlankSpace($file_ext) !== true ? '.'.$file_ext : '');

            // check if file was received
            if($request->hasFile($file_input) !== true)
                throw new Exception('File was not received');

            // check disk
            $disk_info = CLHF::STORAGE_DiskInfo($disk_name, true, null);  // can throw error
            $disk = Storage::disk($disk_name);
            $file_dir = $disk_info['root'];
            $file_path = AppFn::STR_Sanitize($file_dir.'/'.$file_name);
            $file_url = CLHF::STORAGE_FileURL($file_path, 'dispose');

            // check file 
            if(AppFn::STR_IsBlankSpace($file_basename) === true)
                throw new exception('File name value is empty');

            // check file extension from client
            $file_ext_client = AppFn::STR_UTF8CC($request->file($file_input)->extension(), 'lower');
            if(in_array($file_ext_client, $valid_extensions) !== true) {
                throw new exception('Invalid file extension ('.$file_ext_client.')');
            }

            // try unlinking
            if(File::exists($file_path) === true) {
                if($deleteExistingFile !== true)
                    throw new Exception('File already exists');
                $del = File::delete($file_path);
                if($del !== true)
                    throw new exception('Unlink failed');
            }

            // execute closure
            if(AppFn::is_closure($callback_before)) {                
                $err_msg = $callback_before->__invoke($request->file($file_input)->getPathname()) ?? '';  // temporary file path
                if(AppFn::STR_IsBlankSpace($err_msg) !== true)
                    throw new exception($err_msg);
            }

            // uploading file and check if exists
            $file_move = $request->file($file_input)->move($file_dir, $file_name);  // can throw error
            if($disk->exists($file_name) !== true)
                throw new exception('File creation failed');
            

        } catch(\Exception $ex) {
            $output[1] = $ex->getMessage();//.$ex->getLine();
            goto point1;
        }
        $output[0] = true;
        /*$output[2] = [
            'file' => [
                'name'              => $file_name,
                'basename'          => $file_basename,
                'extension'         => $file_ext,
                'directory'         => $file_dir,
                'path'              => $file_path,
                'url'               => $file_url,
            ],
            'extension_client'  => $file_ext_client,
            'file_input'        => $file_input,
            'disk_name'         => $disk_name,
        ];*/
        $output[2] = CLHF::STORAGE_FileInfo($file_path, '');
        //$output[2]['extension_client'] = $extension_client;
        $output[2]['input'] = $file_input;
        $output[2]['disk'] = $disk_name;
        $output[2]['url'] = CLHF::STORAGE_FileURL($file_path, 'dispose');

        point1:
        return $output;
    }

    
    

    public static function STORAGE_FileInfo(string $path, $basename_new=null) {
        /*
            $basename_new:
                === true ? same as $basename
                !empty() ? $basename_new
                else ? random 15 alphanum
        */

        $file = [
            'exists' => false,
            'path' => $path,
            'dir' => '',
            'name' => '',
            'basename' => '',
            'ext' => '',
            'mime_type' => null,
            'path_app' => '',  // relative path inside storage/app
            'dir_app' => '',
            'md5' => '',
        ];

        // CHECK IF PATH EXISTS
        $file['exists'] = (Str::of($file['path'])->trim()->__toString()!=='' && File::exists($file['path']) && is_file($file['path']));
        //if($file['exists'] !== true)
        //    throw new exception('File doesn\'t exists');               

        // CHECK MIME TYPE
        $file['mime_type'] = false;
        try { $file['mime_type'] = File::mimeType($file['path']); } catch(\Exception $ex) {}
        $file['mime_type'] = !is_string($file['mime_type']) ? '' : $file['mime_type'];
        //if(is_string($file['mime_type']) !== true || AppFn::STR_IsBlankSpace($file['mime_type']) === true)
        //    throw new exception('Invalid mime type');

        // FILE INFO PARTS
        //$file['dir'] = File::dirname($file['path']);
        $file['name'] = basename($file['path']);
        $file['dir'] = Str::of(Str::replaceLast($file['name'], '', $file['path']))->rtrim('/')->__toString();  
        $file['ext'] = Str::afterLast($file['name'], '.');
        $file['ext'] = ($file['name'] === $file['ext']) ? '' : $file['ext'];
        $file['basename'] = Str::replaceLast('.'.$file['ext'], '', $file['name']);  
        $file['path_app'] = Str::replaceLast(storage_path('app/'), '', $file['path']);
        $file['dir_app'] = Str::of(Str::replaceLast($file['name'], '', $file['path_app']))->rtrim('/')->__toString();
        $file['md5'] = $file['exists'] ? md5_file($file['path']) : '';
        
        // NEW PATH
        $ext_ = AppFn::STR_IsBlankSpace($file['ext']) !== true ? '.'.$file['ext'] : '';
        if($basename_new === true)
            $file['path_new'] = $file['name'];
        else if(is_string($basename_new) && AppFn::STR_IsBlankSpace($basename_new) !== true)
            $file['path_new'] = $basename_new.$ext_;
        else
            $file['path_new'] = AppFn::STR_GenerateRandomAlphaNum(15).$ext_;

        return $file;
    }


    public static function STORAGE_FileStream(Request $request) {
        /*
            URL PARAMS:
                p => path (encrypted)
                m => mode (int)
                    1 => dispose
                    2 => download
        */

        // SETTINGS
        $modes = ['dispose', 'download'];
        $base_dir = storage_path('app/');
        // $url_data = AppFn::URL_parse($request->fullUrl());
        $m = ((int)($request->get('m') ?? 0)) - 1;
        $p = (string)($request->get('p') ?? '');

        // CHECK STREAM MODE
        $mode = $modes[$m] ?? '';  // m -> stream mode
        if(in_array($mode, $modes, true) !== true)
            throw new Exception('Invalid stream mode');

        // CHECK FILE PATH INFO
        $crypt = CLHF::SECURITY_crypt($p, 1, true);
        if($crypt[0] !== true)
            throw new exception($crypt[1]);
        $path = $base_dir.$crypt[2];

        // CHECK FILE PATH AND MIME TYPE
        $file = CLHF::STORAGE_FileInfo($path, null);
        if($file['exists'] !== true)
            throw new Exception('File doesn\'t exists');
        if(AppFn::STR_IsBlankSpace($file['mime_type']) === true)
            throw new Exception('Invalid mime type');
                
        // ROLE VALIDATION
        $hasAccess = StorageAccess::check($request, $file);
        if($hasAccess !== true)
            throw new exception('Access denied');

        // FILE STREAM
        $headers = ['Content-Type: '.$file['mime_type'], 'Cache-Control: no-cache, no-store, must-revalidate, post-check=0, pre-check=0'];
        $dispositions = ['inline', 'attachment'];
        $disposition = $dispositions[$m];
        $filestream = Response::download($file['path'], $file['path_new'], $headers, $disposition);

        return $filestream;
    }









    /**
     * Encrypt/Decrypt a value
     *
     * @param integer|string $val
     * @param integer $mode
     * @param boolean $serialize
     * @return array(boolean,string,string) [is_success,msg,data]
     */
    public static function SECURITY_crypt($val, int $mode, bool $serialize=true) {
        $opt = [false, '', ''];  // [is_success, msg, data]
        try {
            if($mode === 0) {
                if(is_array($val))  $val = json_encode($val);
                $opt[2] = encrypt($val, $serialize);
            }
            else if($mode === 1)
                $opt[2] = decrypt($val, $serialize);
            else
                throw new Exception('Invalid mode');
        } catch(\Exception $ex) {
            $opt[1] = $ex->getMessage();
            goto point1;
        }
        $opt[0] = true;
        point1:
        return $opt;
    }





    # ----------------------------------------------------------
    # / ROUTE
    # ----------------------------------------------------------

    public static function ROUTE_GenerateURL(string $route, array $param=[], bool $absolute=true) {
        if(!Route::has($route))
            throw new Exception('Invalid route name');
        return route($route, $param, $absolute);
    }

    public static function ROUTE_GenerateURLSelf(array $param=[], bool $absolute=true) {
        return SELF::ROUTE_GenerateURL(request()->route()->getName(), $param, $absolute);
    }

    # ----------------------------------------------------------
    # \ ROUTE
    # ----------------------------------------------------------








    # ----------------------------------------------------------
    # \ OTHER FUNCTIONS
    # ----------------------------------------------------------


    public static function DATATABLE_morph($cols, $rows, $params) {
        $cols2 = [];
        $x = -1;
        foreach($cols as $key=>$val) {
            $x++;
            $cols2[$x] = $val;
            $cols2[$x]['data'] = $key;
            $cols2[$x]['orderable'] = array_key_exists('orderable', $cols2[$x]) ? (bool)$cols2[$x]['orderable'] : false;
        }

        return [
            '_cols' => $cols2,
            '_rows' => $rows,
        ];
    }

    /**
     * Parses datatable client request query with analysis
     *
     * @param Request $request
     * @return array
     */
    public static function DATATABLE_ParseClient(Request $request) {
        if(!$request->isMethod('GET'))
            throw new exception('Invalid request method');
        
        $draw = (int)$request->query('draw');
        $length = (int)$request->query('length');
        $start = (int)$request->query('start');
        $pageNum = ($start <= 0 ? 0 : ($length / $start)) + 1;
        $_ = (string)$request->query('_');
        
        // order
        $order = [];
        foreach(($request->query('order') ?? []) as $key=>$val) {
            $order[$key] = [
                'column' => (int)$val['column'],
                'dir' => $val['dir'],
            ];
        }

        // columns 
        $columns = [];
        foreach(($request->query('columns') ?? []) as $key=>$val) {
            $columns[$key] = [
                'data' => (string)$val['data'],
                'name' => $val['name'],
                'searchable' => (bool)$val['searchable'] ?? false,
                'orderable' => (bool)$val['orderable'] ?? false,
                'search' => [
                    'value' => $val['search']['value'],
                    'regex' => $val['search']['regex'],
                ],
            ];
        }

        // search
        $search_ = $request->query('search') ?? [];
        $search = [
            'value' => $search_['value'] ?? '',
            'regex' => $search_['regex'] ?? '',
        ];
        $hasSearchValue = !AppFn::STR_IsBlankSpace((string)$search['value']);

        return compact(
            'draw',
            'order',
            'columns',
            'start',
            'length',
            'search',
            '_',

            // added
            'pageNum',
            'hasSearchValue',
        );
    }

    public static function SSP_Generate($model, $data) {
        //options: db, dt, formatter, sortable, label, class, same_as=>key
        $func_opt = function(array $arr) {
            $opt = [];
            if(array_key_exists('formatter', $arr))   $opt['formatter'] = $arr['formatter'];
            if(array_key_exists('label', $arr))       $opt['label'] = $arr['label'];            
            if(array_key_exists('class', $arr))       $opt['class'] = $arr['class'];
            if(array_key_exists('sortable', $arr))    $opt['sortable'] = $arr['sortable'];
            return $opt;
        };
        $ascendancy = [];
        foreach($data as $key1=>$val1) {
            $ascendancy[] = $key1;
        }
        $dt = [];
        $x = -1;
        foreach($data as $key1=>$val1) {
            $x++;
            $dt[$x]['db'] = $key1;
            $dt[$x]['dt'] = $x;
            if(array_key_exists('same_as', $val1)) {
                // analyze ascendancy
                $ord_cur = array_search($key1, $ascendancy);  // get order # of target
                $ord_tar = array_search($val1['same_as'], $ascendancy);  // get order # of current
                if($ord_cur === false) throw new exception('Array key `'.$key1.'` doesn\'t exists');
                if($ord_tar === false) throw new exception('Array key `'.$val1['same_as'].'` doesn\'t exists');
                if($ord_cur <= $ord_tar) throw new exception('Target key `'.$val1['same_as'].'` must be in the upper order.');
                //$dt[$x]['db'] = $dt[$ord_tar]['db'];
                $dt[$x] = array_merge($dt[$x], $func_opt($dt[$ord_tar]));
            } else {
                $dt[$x] = array_merge($dt[$x], $func_opt($val1));
            }
        }
        //dd($dt);
        
        //$dt_obj = new \SoulDoit\DataTable\SSP($model, $dt);  // use SoulDoit\DataTable\SSP;
        $dt_obj = new \SoulDoit\DataTable\SSP($model, $dt);  // use SoulDoit\DataTable\SSP;
        // dd($dt_obj);
        //$dt_arr = $dt_obj->getDtArr();
        return $dt_obj;
    }

    


    # ----------------------------------------------------------
    # / OTHER FUNCTIONS
    # ----------------------------------------------------------







}



