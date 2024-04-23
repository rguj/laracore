<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Spatie\Url\Url as SpatieUrl;
use Rguj\Laracore\Request\Request;
use Rguj\Laracore\Middleware\ClientInstanceMiddleware;
use Rguj\Laracore\Library\StorageAccess;
use App\Models\User;
use phpDocumentor\Reflection\PseudoTypes\True_;
use Rguj\Laracore\Exception\CustomJSONException;



/* -----------------------------------------------
 * DEFINERS
 * - prefixed by `BH_` -> Base Helper
 */

define('BH_ENV_KEY', 'env');
define('BH_UNV_KEY', 'unv');
define('BH_CRUD_KEY', 'crud');


/* -----------------------------------------------
 * REQUIRE
 */

if(file_exists(__DIR__.'/CurrentUserHelper.php'))
    require_once __DIR__.'/CurrentUserHelper.php';

if(file_exists(__DIR__.'/ThemeUtilHelper.php'))
    require_once __DIR__.'/ThemeUtilHelper.php';












/** -----------------------------------------------
 * SHORTCUTS
 */

/**
 * Just a dummy function
 *
 * @return void
 */
function _____()
{

}

/**
 * Evaluates the condition and returns the respective `true|false` value
 *
 * @param string $key
 * @param boolean $strict
 * @return mixed
 * @source `cond_return()` Evaluates the condition and returns the respective `true|false` value
 */
function cr(bool $cond, $true, $false)
{
    return cond_return($cond, $true, $false);
}

/**
 * Throws a readable JSON Exception from a variable
 *
 * @param mixed $var
 * @param boolean $assoc
 * @return mixed
 * @source `exception_json` Throws a readable JSON Exception from a variable
 */
function ej($var, bool $assoc = true)
{
    exception_json($var, $assoc);
}

/**
 * JSON echo & die
 *
 * @param mixed $var
 * @param bool $withTrace
 * @return void
 * @source `json_echo_and_die()` JSON Echo & Die
 */
function jed($var, bool $withTrace = false)
{
    json_echo_and_die($var, $withTrace);
}

/**
 * Throw new exception variable in a string form
 *
 * @param mixed $var
 * @throws Exception
 * @return Exception
 */
function tnev($var = null)
{
    throw new exception((string)$var);
}

/**
 * Gets the view variable
 *
 * @param string $key
 * @param boolean $strict
 * @return mixed
 * @source `view_variable()` Gets the view variable
 */
function vv(string $key, bool $strict = false)
{
    return view_variable($key, $strict);
}













/** -----------------------------------------------
 * ARRAYS
 */

/**
 * Get an item from an array using "dot" notation.
 *
 * @param  array  $array
 * @param  string|int|null  $key
 * @param  mixed  $default
 * @return mixed
 */
function arr_get($array, $key, $default = null)
{
    return Arr::get($array, $key, $default);
}

/**
 * Set an array item to a given value using "dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 *
 * @param  array  $array
 * @param  string|int|null  $key
 * @param  mixed  $value
 * @return array
 */
function arr_set(&$array, $key, $value)
{
    return Arr::set($array, $key, $value);
}

function arr_prefix(array $arr, string $prefix='') {
    $o = [];
    if(empty($prefix))
        return $o;
    foreach($arr as $k=>$v) {
        $o[$k] = $prefix.$v;
    }
    return $o;
}

/**
 * Gets the depth level of the deepest element in an array.
 *
 * @param array $array
 * @return int
 */
function arr_depth(array $array)
{
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

/**
 * Get array structure type. Supports only one dimension.
 *
 * @param array $arr
 * @return string
 */
function arr_type(array $arr)
{
    if(arr_depth($arr) !== 1) throw new Exception('Must be a 1 dimensional array');
    return arr_structure($arr)[0];
}

/**
 * Check if the array structure is sequential
 * - e.g. `[0=>'hello', 1=>'world', 2=>'sample']`
 * - checks only the first dimension
 *
 * @param array $arr
 * @return bool
 */
function arr_type_seq(array $arr) {
    return arr_type($arr) === 'sequential';
}

/**
 * Check if the array structure is associative
 * - e.g. `[a=>'hello', b=>'world', 0=>'sample']`
 * - checks only the first dimension
 *
 * @param array $arr
 * @return bool
 */
function arr_type_assoc(array $arr) {
    return arr_type($arr) === 'associative';
}

/**
 * Parses array and returns the structure
 * - structure types: `empty`, `sequential`, `associative`, `mixed`, `irregular`
 * - supports up to 2 dimension only
 * - auto converts `null` => `''`, `false` => `0`, `true` => `1`, `decimals` => `integer`
 * - associative can be `int` or `string` that doesn't match the counter sequentially
 *
 * @param array $arr
 * @return array
 */
function arr_structure(array $arr)
{
    $dimensions = ['empty', 'sequential', 'associative', 'mixed', 'irregular'];
    $struc = [$dimensions[0], $dimensions[0]];

    if(!is_array($arr))
        throw new Exception('$arr must be array');
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
    $dim1 = [];     // dimension 1
    $dim2 = [];     // dimension 2
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
                    throw new Exception('Could not handle beyond 2 dimensions.');
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
                throw new Exception('Array value not found.');
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

/**
 * Checks if a value exists in an array column
 *
 * @param mixed $needle
 * @param array $haystack
 * @param string $col_key
 * @param boolean $strict
 * @return bool
 */
function arr_colval_exists($needle, array $haystack, string $col_key, bool $strict = false)
{
    return in_array($needle, array_column($haystack, $col_key), $strict);
}

/**
 * Check if an item or items exist in an array using "dot" notation.
 *
 * @param  \ArrayAccess|array  $array
 * @param  string|array  $keys
 * @return bool
 */
function arr_has($array, $keys)
{
    return Arr::has($array, $keys);
}

/**
 * Searches and gets the row where `$value` matches the `$key` value
 *
 * @param array $array
 * @param mixed $key
 * @param mixed $value
 * @return array
 */
function arr_search_by_key(array $array, $key, $value) {
    if(!is_array($array)) {
        return [];
    }
    $results = [];
    foreach($array as $element) {
        if(isset($element[$key]) && $element[$key] == $value) {
            $results[] = $element;
        }
    }
    return $results;
}

function arr_search_column(array $a, $column, $val) {
    $c = array_search($val, array_column($a, $column), true);
    if($c === false) {
        return [];
    }
    return $a[$c];
};












/** -----------------------------------------------
 * AUTH
 */

/**
 * Check if user_id has role/s
 *
 * This doesn't throw exception
 *
 * @param int $user_id
 * @param int|string|array $roles
 * @return bool
 */
function auth_is_authorized(int $user_id, $roles, bool $flag = false) {
    $bool = false;
    $cacheRoles = (array)config('roles');

    if(!is_int($user_id) || $user_id <= 0)
        goto point1;

    $db_role_ids = DB::table('unv_role_user')->where(['user_id' => $user_id])->pluck('role_id')->toArray();

    // reform $roles
    if(!(is_int($roles) || is_string($roles) || is_array($roles)))
        goto point1;

    if(is_string($roles)) {
       $roles = [trim($roles)];
    }

    // eliminate invalid $roles entries
    $roleIDs = [];
    foreach($roles as $k=>$v) {
        if(is_string($v)) {
            $search1 = arr_search_by_key($cacheRoles, 1, $v);
            $search2 = arr_search_by_key($cacheRoles, 2, $v);
            if(!empty($search1)) $roleIDs[] = $search1[0];
            if(!empty($search2)) $roleIDs[] = $search2[0];
        }
        elseif(is_int($v)) {
            $search3 = arr_search_by_key($cacheRoles, 0, $v);
            if(!empty($search3)) $roleIDs[] = $search3[0];
        }
    }
    // $roleIDs = array_unique($roleIDs);
    $user_role_ids = array_unique(array_column($roleIDs, 0));
    sort($user_role_ids);
    $success = !empty($db_role_ids) && !empty($user_role_ids);
    foreach($user_role_ids as $k=>$v) {
        if(!in_array($v, $db_role_ids, true)) {

            $success = false;
            break;
        }
    }
    $bool = $success;

    point1:
    return $bool;
}

/**
 * Gets the user statuses
 *
 * @param int $user_id
 * @return <int,bool> `[user_exists, is_active, is_verified, passed_all]`
 */
function auth_user_status(int $user_id)
{
    $user_exists = $is_active = $is_verified = $passed_all = false;
    if(empty($user_id))
        goto point1;
    $user = (array)config('user');
    $email = (string)($user['email'] ?? '');
    $user_exists = !empty($user) && !empty($email);
    $is_active = ((int)($user['state']['is_active'] ?? 0)) === 1;
    $is_verified = ((int)($user['verify']['email']['is_verified'] ?? 0)) === 1;
    $passed_all = $user_exists && $is_active && $is_verified;
    point1:
    return [$user_exists, $is_active, $is_verified, $passed_all];
}



























/** -----------------------------------------------
 * BLADE
 */

/**
 * Get all of the shared data for the environment.
 *
 * @return array
 * @static
 * @requires `\Illuminate\Support\Facades\View`
 */
function blade_get_with()
{
    return View::getShared();
}

/**
 * Generate the URL to a named route.
 *
 * @param array|string $name
 * @param mixed $parameters
 * @param boolean $absolute
 * @return string
 */
function blade_route($name, $parameters = [], $absolute = true)
{
    return route($name, $parameters, $absolute);
}

/**
 * Renders HTML code for session attribute error
 *
 * @param string $key
 * @return string
 */
function blade_error(string $key)  // render attr errors
{
    if(!session()->has('errors')) return '';
    $errors = session('errors')->get($key) ?? [];
    if(empty($errors)) return '';
    $str = '<div class="text-danger lbl-error-msg">';
    foreach($errors as $error) {
        $str .= '<div class="mt-2">'.$error.'</div>';
    }
    $str .= '</div>';
    return $str;
}

/**
 * Returns form purpose from `$with`.
 *
 * - useful in blade template
 *
 * @param string $purpose
 * @param integer $index `0` => `value`, `1` => `input_with_value`
 * @return string
 */
function blade_purpose(string $purpose, int $index = 0)
{
    $vars = blade_get_with();
    return (string)json_encode(
        !array_key_exists('with', $vars)
        ? ''
        : (string)arr_get($vars, 'with.form.purposes.'.$purpose.'.'.$index, '')
    );
}











/* -----------------------------------------------
 * CLASS
 */

/**
 * Invokes non-static class method
 *
 * @param string $class
 * @param string $method
 * @param array $parameters
 * @return mixed
 */
function class_method_unstatic(string $class, string $method, array $parameters)
{
    if(!class_exists($class))
        throw new exception('Class doesn\'t exists: '. $class);
    return (new ($class))->$method(...$parameters);
}

/**
 * Invokes a class method
 *
 * - this will not work for non-controller class
 *
 * @param string $class
 * @param string $method
 * @param array $args
 * @param boolean $strict
 * @return void
 */
function class_controller_method(string $class, string $method, array $args = [], string $resolveRequest = '', bool $strict = false)
{
    $ret = null;
    if(empty($class))
        throw new exception('$class is empty');
    if(!class_exists($class))
        throw new exception('Class doesn\'t exists: '. $class);
    if(!method_exists($class, $method))
        throw new Exception('Method `'.$method.'` doesn\'t exists in `'.$class.'`');

    // reflect method information
    $ref = new \ReflectionMethod($class, $method);
    $params = $ref->getParameters();
    if(empty($params)) goto point1;

    $firstParam = $params[0];
    list($name, $type) = [$firstParam->getName(), $firstParam->getType()->getName()];

    // check parent, skip if parents are built-in
    $parents = (!empty($type) && !in_array($type, var_types(), true) && class_exists($type)) ? class_parents($type) : '';
    // $requiredParent = 'Rguj\Laracore\Request\Request';
    $requiredParent = 'Symfony\Component\HttpFoundation\Request';
    if(!array_key_exists($requiredParent, $parents)) {
        if($strict) throw new exception('Required class parent (first argument): '.$requiredParent);
        goto point1;
    }

    // remove first arg if its Request
    $isFirstArgReq = false;
    $firstArg = $args[0] ?? null;
    if(!is_null($firstArg)) {
        // if(is_string($firstArg))      $firstArg = $firstArg;
        if(is_string($firstArg))      {}
        else if(is_object($firstArg)) $firstArg = $firstArg::class;
        if(array_key_exists('Symfony\Component\HttpFoundation\Request', class_parents($firstArg))) {
            $isFirstArgReq = true;
            array_shift($args);
        }
    }

    // insert request object
    if(!empty($resolveRequest) && !class_exists($resolveRequest))
        throw new exception('Inexistent class: '.$resolveRequest);
    elseif(empty($resolveRequest) && $isFirstArgReq) {
        $resolveRequest = $firstArg;
    }
    $toResolve = !empty($resolveRequest) ? $resolveRequest : $type;

    // check if request types match
    if($isFirstArgReq && ($toResolve !== $type)) {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1] ?? [];
        $file = $bt['file'] ?? '';
        $function = $bt['function'] ?? '';
        $line = $bt['line'] ?? '';
        $trace = str_sanitize($file.(!empty($function) ? '::'.$function.'()' : '').(!empty($line) ? ' (line '.$line.')' : ''));
        $trace = (!empty($trace) ? ' Trace: '.$trace : '');
        throw new exception('Type mismatch. Expected '.$type.', given '.$toResolve.'.'.$trace);
    }

    $req = resolve($toResolve);
    array_unshift($args, $req);

    point1:
    return (new $class)->$method(...$args);
}































/**
 * Validates view component data `$with`
 *
 * @param array $config
 * @param array $UID
 * @param integer $check_mode
 * @return <int, bool|string>
 */
function component_data_validate(array $config, array $UID, int $check_mode = 0)
{
	$output = [false, ''];
	try {
		if(!in_array($check_mode, [0, 1, 2]))  // [all, config only, uid only]
			throw new exception('$check_mode must be 0, 1, or 2');

		// config & UID
		$bool1 = (
			arr_depth($config) >= 1
			&& array_key_exists('name', $config) && str_filled($config['name'])
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
			arr_depth($UID) >= 1
			&& array_key_exists('rules', $UID)
			&& array_key_exists('preloads', $UID)
			&& array_key_exists('values', $UID)
			&& array_key_exists('errors', $UID)
		);

		if(!$bool1 && in_array($check_mode, [0, 1])) throw new Exception('Invalid array structure `$config`');
		if(!$bool2 && in_array($check_mode, [0, 2])) throw new Exception('Invalid array structure `$uid`');
		$output[0] = true;
	} catch(\Exception $ex) {
		$output[1] = $ex->getMessage();
	}
	return $output;
}

/**
 * Analyzes component data and arguments for blade rendering
 *
 * @param array $data
 * @param array $args
 * @return array
 */
function component_analysis($data, $args)
{

	// $data component data
	// $args [config[...], form[preloads, fieldrules, defaults, errors, ]]

	// can accept one or multiple configs

	if(!is_array($args) || count($args) !== 2)
		throw new Exception('$args must be an array of 2 (config & with)');

	$configs = $args[0];
	$form = $args[1]['form'];//$args[1];

	$APP_VSK_NAME = config('z.base.vsk.name');
	if(str_empty($APP_VSK_NAME))
		throw new Exception('VSK value is empty');

	if(empty($configs))
		throw new exception('Must have at least 1 config array');

	$componentName = $data['componentName'];
	$attributes = obj_reflect($data['attributes'], true)['attributes'];
	$slot = obj_reflect($data['slot'], true)['html'];

	// unify form subkeys
	foreach(['preloads', 'rules', 'values', 'errors', ] as $k=>$v) {
		if(array_key_exists('field_'.$v, $form)) {
			$form[$v] = $form['field_'.$v];
			unset($form['field_'.$v]);
		}
	}

	$PRELOADS   = $form['preloads'] ?? [];
	$RULES      = $form['rules'] ?? [];
	$VALUES     = $form['values'] ?? [];
	$ERRORS     = $form['errors'] ?? [];

	$ANALYZER = function(int $num, array $config) use($componentName, $attributes, $slot, $form, $ERRORS, $PRELOADS, $RULES, $VALUES, $APP_VSK_NAME) {

		// validate config and form (User Interaction Data)
		$validation = component_data_validate($config, $form);
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
		$config2['placeholder_s2'] = str_filled_eval_self($config2['placeholder'], ' '); // alt code 255
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
			// $vsk = old($APP_VSK_NAME.'.'.$attr_name, '');
            $vsk = '';
            if(session()->has(config('z.base.error.key').'.'.$attr_name))
                $vsk = 'error';
            elseif(session()->has(config('z.base.success.key').'.'.$attr_name))
                $vsk = 'success';

			$fg_state = 'fg-normal';
			if($vsk === 'error')
				$fg_state = ($config2['bool']['is_showable_error'] ? 'fg-error' : 'fg-normal');
			else if($vsk === 'success')
				$fg_state = ($config2['bool']['is_showable_success'] ? 'fg-success' : 'fg-normal');
			return $fg_state;
		};

		$is_af = function($val) use($config2) {
			$af_val = is_string($val) ? str_sanitize($val) : $val;
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
		$hinter_msg .= str_filled($config['hinter_lbl']) ? '<br>'.$config['hinter_lbl'] : '';
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

		// $attr_hinter_ipt = 'data-theme="dark" data-trigger="focus" data-html="true" title="'.$config2['hinter']['input'].'"';
		// $attr_hinter_lbl = 'data-theme="dark" data-trigger="focus hover" data-html="true" title="'.$config2['hinter']['label'].'"';
		$attr_hinter_ipt = 'data-bs-trigger="focus" data-bs-placement="auto" data-bs-html="true" title="'.$config2['hinter']['input'].'"';
		$attr_hinter_lbl = 'data-bs-trigger="focus hover" data-bs-html="true" title="'.$config2['hinter']['label'].'"';
		$html_hinter_lbl = '<span data-bs-toggle="tooltip" '.$attr_hinter_lbl.'><i class="mr-1 ml-1 fas fa-question-circle" style="font-size: 14px;"></i></span>';
		$html_required = $config2['bool']['is_required'] ? '<span class="text-danger" title="Required">*</span>' : '';


		$config2['attr'] = [
			'maxlength'      => $config2['max']>0 ? 'maxlength='.$config2['max'].'' : '',
			'placeholder'    => !empty($config2['placeholder']) ? 'placeholder="'.$config2['placeholder'].'"' : '',
			'required'       => $config2['bool']['is_required'] ? 'required' : '',
			'autocomplete'   => 'autocomplete='.($config2['bool']['is_autocomplete'] ? 'on' : 'off').'',
			'autofocus'      => $config2['bool']['is_autofocus'] ? 'autofocus' : '',
			'hinter_input'   => str_filled($config2['hinter']['input']) ? $attr_hinter_ipt : '',
			'others'         => '',

		];
		$config2['html'] = [
			'label' => '',
			//'required'       => $html_required,
			//'hinter_label'   => str_filled($config2['hinter']['label']) ? $html_hinter_lbl : '',
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
	return $analysis2;
}












/** -----------------------------------------------
 * COND - CONDITION
 */

/**
 * Evaluates the condition and returns the respective `true|false` value
 *
 * @param boolean $cond
 * @param mixed $true
 * @param mixed $false
 * @return mixed
 */
function cond_return(bool $cond, $true, $false)
{
    return $cond ? $true : $false;
}

















/** -----------------------------------------------
 * CONFIG
 */

/**
 * Get the specified configuration value.
 *
 * @param array|string $key
 * @param mixed $default
 * @return mixed
 */
function config_get($key, $default = null, string $forceType = '')
{
    $ret = config()->get($key, $default);
    if(!empty($forceType))
        $ret = var_cast($ret, $forceType);
    return $ret;

}

/**
 * Set a given configuration value.
 *
 * @param array|string $key
 * @param mixed $value
 * @return void
 */
function config_set($key, $value = null)
{
    return config()->set($key, $value);
}

/**
 * Set key => value on universal config
 *
 * @param string $key
 * @param mixed $val
 * @return void
 * @deprecated 1.0.0
 */
function config_unv_set(string $key, $val)
{
    config()->set(BH_UNV_KEY.'.'.$key, $val);
}

/**
 * Get environment config
 *
 * @param string $key
 * @param mixed $val
 * @return mixed
 * @deprecated 1.0.0
 */
function config_env($key = null, $default = null) {
	return config(BH_ENV_KEY.(!is_null($key) ? '.'.$key : ''), $default);
}

/**
 * Get universal config
 *
 * @param string $key
 * @param mixed $val
 * @return mixed
 */
function config_unv($key = null, $default = null) {
	return config(BH_UNV_KEY.(!is_null($key) ? '.'.$key : ''), $default);
}















/** -----------------------------------------------
 * CRYPT - SECURITY
 */

/**
 * Encrypt/Decrypt a value
 *
 * @param int|string $val
 * @param int $mode (0 => encrypt, 1 => decrypt)
 * @param bool $serialize
 * @return array [is_success, msg, data]
 */
function crypt_sc($val, int $mode, bool $serialize=true) {
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

/**
 * Encrypt a value
 *
 * @param mixed $val
 * @param bool $serialize
 * @return mixed
 */
function crypt_en($val, bool $serialize = true)
{
    $crypt = crypt_sc($val, 0, $serialize);
    if(!$crypt[0]) throw new exception($crypt[1]);
    return $crypt[2];
}

/**
 * Decrypt a value
 *
 * @param mixed $val
 * @param bool $unserialize
 * @return mixed
 */
function crypt_de($val, bool $unserialize = true)
{
    $crypt = crypt_sc($val, 1, $unserialize);
    if(!$crypt[0]) throw new exception($crypt[1]);
    return $crypt[2];
}

/**
 * Decrypts purpose, updates the request data, and validates if it's updated.
 *
 * @param Request $request
 * @param string $key
 * @param bool $is_post true ? input() : query()
 * @return bool if update succeeded
 */
function crypt_de_merge_get(Request &$request, string $key, bool $is_post, bool $strict = true)
{
    if(!$request->has($key)) {
        if($strict) {
            throw new exception('Non-existent request key: '.$key);
        }
        return false;
    } else {
        $crypt1 = crypt_sc(($is_post ? $request->input($key) : $request->query($key)), 1, true);
        if($crypt1[0])
            $request->merge([ $key => $crypt1[2] ]);
        return $request->get($key) === $crypt1[2];
    }
}















/** -----------------------------------------------
 * DATATABLE
 */

/**
 * Gets the parsed column definition of datatable
 *
 * @param Request $request
 * @param array $columns
 * @return array
 */
function datatable_columns(Request $request, array $columns)
{
    $d = datatable_request_parse($request, $columns);
    return (array)($d['col'] ?? []);
}

/**
 * Parses datatable client request query with analysis
 *
 * @param Request $request
 * @param array $columns
 * @return array `$pr` parsed request
 */
function datatable_request_parse(Request $request, array $columns) {
    if(!$request->isMethod('GET'))
        throw new exception('Invalid request method');

    $req2 = $request;  // deep copy request
    $req2->merge(compact('columns'));  // merge columns

    $draw = (int)$req2->query('draw');      // draw requests
    $length = (int)$req2->query('length');  // items limit
    $start = (int)$req2->query('start');    // item start
    $search_ = (array)($req2->query('search') ?? []);  // global search keywords
    $_ = (string)$req2->query('_');
    $columnsRaw = (array)($req2->query('columns') ?? []);
    $order = (array)($req2->query('order') ?? []);

    // $start = $start <= 0 ? 1 : $start;
    $start = $start <= 0 ? 0 : $start;
    $length = $length <= -1 ? -1 : $length;
    // $itemStartClient = $start * $length;
    $itemStartClient = $start * $length;
    // $columnsRaw = (array)($req2->query('columns') ?? []);
    $columnsClient = $columnsServer = [];

    // reform order
    // order [ [ column => '', dir => 'asc|desc' ] ]
    $order = [];
    foreach(($req2->query('order') ?? []) as $key=>$val) {
        $order[$key] = [
            'column' => (int)$val['column'],
            'dir' => $val['dir'],
        ];
    }

    $func_opt = function(array $arr) {
        $opt = [];

        $opt['attr'] = (string)arr_get($arr, 'attr');
        $opt['db'] = (string)arr_get($arr, 'db');
        $opt['label'] = (string)arr_get($arr, 'label');

        $opt['searchable'] = (bool)arr_get($arr, 'searchable');
        $opt['sortable'] = (bool)arr_get($arr, 'sortable');
        $opt['class'] = (string)arr_get($arr, 'class');

        $opt['type'] = (string)arr_get($arr, 'type');
        $opt['type'] = $opt['type'] === 'int' ? 'integer' : $opt['type'];
        $opt['type'] = !in_array($opt['type'], ['integer', 'string']) ? 'string' : $opt['type'];

        $opt['frontend_type'] = array_key_exists('frontend_key', $arr) ? (string)$arr['frontend_key'] : 'string';

        $opt['search'] = [
            'value' => (string)arr_get($arr, 'search.value'),
            'regex' => (string)arr_get($arr, 'search.regex'),
        ];

        $opt['formatter'] = arr_get($arr, 'formatter');
        if(!is_callable($opt['formatter']) || is_null($opt['formatter']('sample'))) {
            unset($opt['formatter']);
        }
        // $opt['formatter'] = is_callable($opt['formatter']) && !is_null($opt['formatter']('sample')) ? $opt['formatter'] : null;

        $opt['same_as'] = str_sanitize((string)arr_get($arr, 'same_as'));
        $opt['same_as'] = !empty($opt['same_as']) ? $opt['same_as'] : null;
        $opt['visible'] = array_key_exists('visible', $arr) ? (bool)arr_get($arr, 'visible') : true;

        if(array_key_exists('width', $arr)) {
            $opt['width'] = (string)arr_get($arr, 'width');
        }

        if(array_key_exists('default_content', $arr)) {
            $opt['default_content'] = (string)arr_get($arr, 'default_content');
        }

        return $opt;
    };

    // record ascendancy
    $ascendancy = [];
    foreach($columnsRaw as $key1=>$val1) {
        $ascendancy[] = $val1['attr'];
    }

    // parse columns
    $x = -1;
    $columns = [];
    $hasSearchColumn = false;
    $columnsClone = [];
    $columnsAttrDB = [];
    $columnsDB = [];
    $columnsDBAlias = [];
    $columnsOriginal = [];
    $columnsAll = [];
    $columnsAttrType = [];
    $columnsFormatter = [];
    $columnsDBBlank = [];
    $dbOrder = [];

    foreach($columnsRaw as $key1=>$val1) {
        $x++;
        $val1['attr'] = str_sanitize($val1['attr']);
        $hasFormatter = false;
        $formatted_value_sample = null;

        // validate attr characters
        if(!str_preg_match('/^([a-zA-Z_])+([0-9])*$/u', $val1['attr']))
            throw new exception('Invalid attribute characters: '.$val1['attr']);

        // parse columns same_as
        if(array_key_exists('same_as', $val1)) {
            // analyze ascendancy
            $ord_cur = array_search($val1['attr'], $ascendancy);  // get order # of target
            $ord_tar = array_search($val1['same_as'], $ascendancy);  // get order # of current
            if($ord_cur === false) throw new exception('Attribute `'.$val1['attr'].'` doesn\'t exists');
            if($ord_tar === false) throw new exception('Parent attribute `'.$val1['same_as'].'` doesn\'t exists');
            if($ord_cur <= $ord_tar) throw new exception('Target key `'.$val1['same_as'].'` must be in the upper order.');
            $columnsClone[$val1['attr']] = $columns[$ord_tar]['attr'];
            $columns[$x] = array_merge($columns[$ord_tar], ['attr' => $val1['attr'], 'dt' => $x, 'same_as' => $columns[$ord_tar]['attr']]);

            if(!empty($columns[$ord_tar]['formatter']) && is_callable($columns[$ord_tar]['formatter'])) {
                $columnsFormatter[$val1['attr']] = $columns[$ord_tar]['formatter'];
            }
        } else {
            $columns[$x]['dt'] = $x;
            $columns[$x]['attr'] = $val1['attr'];
            $columns[$x]['db'] = $val1['db'];
            $columns[$x]['label'] = $val1['label'];
            $columns[$x] = array_merge($columns[$x], $func_opt($val1));

            if(!empty($val1['formatter']) && is_callable($val1['formatter']) && !is_null($val1['formatter']('sample'))) {
                $columnsFormatter[$val1['attr']] = $val1['formatter'];

                // try a sample of formatter
                $hasFormatter = true;
                settype($formatted_value_sample, $columns[$x]['type']);
                $formatted_value_sample = $val1['formatter']($formatted_value_sample);
            }
        }

        // REMOVE LITERAL 'false'
        $columns[$x]['search']['regex'] = strtolower($columns[$x]['search']['regex']) === 'false' ? '' : $columns[$x]['search']['regex'];

        if(!$hasSearchColumn) {
            $hasSearchColumn = str_filled($columns[$x]['search']['value']) || str_filled($columns[$x]['search']['regex']);
        }

        if(str_empty((string)$columns[$x]['same_as']) && !str_empty($columns[$x]['db']) && !array_key_exists($columns[$x]['attr'], $columnsAttrDB)) {
            $columnsAttrDB[$columns[$x]['attr']] = $columns[$x]['db'];
            $columnsDB[] = $columns[$x]['db'];
            // $columnsDBAlias[] = $columns[$x]['db'].' AS '.$columns[$x]['attr'];
        }

        $columnsAttrType[$columns[$x]['attr']] = $columns[$x]['type'];

        // catch db blank
        if(!str_empty($columns[$x]['attr']) && str_empty($columns[$x]['db']) && !in_array($columns[$x]['attr'], $columnsDBBlank, true)) {
            $columnsDBBlank[] = $columns[$x]['attr'];
        }

        if(!str_empty($columns[$x]['attr']) && str_empty((string)$columns[$x]['same_as']) && !in_array($columns[$x]['attr'], $columnsOriginal, true)) {
            $columnsOriginal[] = $columns[$x]['attr'];
        }

        // form frontend data
        $frontend = [];  // ???

        $frontend['data'] = $columns[$x]['attr'];

        if(array_key_exists('label', $columns[$x]))
            $frontend['title'] = trim((string)$columns[$x]['label']);

        $frontend['type'] = trim((string)$columns[$x]['frontend_type']);

        if(array_key_exists('searchable', $columns[$x]))
            $frontend['searchable'] = (bool)$columns[$x]['searchable'];

        if(array_key_exists('sortable', $columns[$x]))
            $frontend['orderable'] = (bool)$columns[$x]['sortable'];

        if(array_key_exists('class', $columns[$x]))
            $frontend['className'] = trim((string)$columns[$x]['class']);

        if(array_key_exists('visible', $columns[$x]))
            $frontend['visible'] = (bool)$columns[$x]['visible'];

        if(array_key_exists('width', $columns[$x]))
            $frontend['width'] = (string)$columns[$x]['width'];

        if(array_key_exists('default_content', $columns[$x])) {
            $frontend['defaultContent'] = trim((string)$columns[$x]['default_content']);
        }

        // if()

        $columnsClient[$x] = $frontend;
    }
    $columnsAll = array_merge($columnsOriginal, array_keys($columnsClone));

    // form db column alias
    foreach($columnsAll as $k=>$v) {
        if(!array_key_exists($v, $columnsAttrDB))
            continue;
        if(array_key_exists($v, $columnsClone)) {
            $columnsDBAlias[] = $columnsAttrDB[$columnsClone[$v]].' AS '.$v;
        } else {
            $columnsDBAlias[] = $columnsAttrDB[$v].' AS '.$v;
        }
    }

    // global search
    $search = [
        'value' => (string)($search_['value'] ?? ''),
        'regex' => (string)($search_['regex'] ?? ''),
    ];
    // REMOVE LITERAL 'false'
    $search['regex'] = strtolower($search['regex']) === 'false' ? '' : $search['regex'];
    $hasSearchGlobal = str_filled($search['value']) || str_filled($search['regex']);

    // db order
    foreach($order as $k=>$v) {
        if(array_key_exists($v['column'], $columnsRaw) && !empty($columnsRaw[$v['column']]['db'])) {
            $dbOrder[$columnsRaw[$v['column']]['db']] = $v['dir'];
        }
    }

    // create where raw (ignoring same_as OR attr with the same db_col_name)
    $searchableColumns = [];
    $searchAttrSpecific = $searchDBSpecific = $searchDBRawSpecific = [];
    $searchAttrGlobal = $searchDBGlobal = $searchDBRawGlobal = [];
    $searchAttrAll = $searchDBAll = $searchDBRawAll = [];
    foreach($columns as $k=>$v) {
        if(($v['searchable'] ?? false)) {

            $a = !empty($v['same_as']) ? $v['same_as'] : $v['attr'];

            // assign each column the search value
            $searchableColumns[] = $v['attr'];
            if(!empty($v['search']['value'])) {
                $searchAttrSpecific[$a][] = $v['search']['value'];
                $searchDBSpecific[$columnsAttrDB[$a]] = $v['search']['value'];
            }
            if(!empty($v['search']['regex'])) {
                $searchAttrSpecific[$a][] = $v['search']['regex'];
                $searchDBSpecific[$columnsAttrDB[$a]] = $v['search']['regex'];
            }

            // assign global search value
            if(!empty($search['value'])) {
                $searchAttrGlobal[$a][] = $search['value'];
                $searchDBGlobal[$columnsAttrDB[$a]] = $search['value'];
            }
            if(!empty($search['regex'])) {
                $searchAttrGlobal[$a][] = $search['regex'];
                $searchDBGlobal[$columnsAttrDB[$a]] = $search['regex'];
            }
        }
    }
    $searchAttrAll = array_merge_recursive($searchAttrSpecific, $searchAttrGlobal);
    $searchDBAll = array_merge_recursive($searchDBSpecific, $searchDBGlobal);

    $searchAttr = [
        'specific' => $searchAttrSpecific,
        'global' => $searchAttrGlobal,
        'all' => $searchAttrAll,
    ];
    $searchDB = [];
    $searchSQL = [
        'lines' => [],
        'query' => '',
        'bindings' => [],
    ];
    $searchDB = [];
    $fullLines = [];

    $prev_k2 = '';
    foreach($searchAttr as $k1=>$v1) {
        foreach($v1 as $k2=>$v2) {
            foreach($v2 as $k3=>$v3) {
                $is_int = $columnsAttrType[$k2] === 'integer';
                $o = $is_int ? '=' : 'LIKE';  // operator
                // $v3_1 = $is_int ? $v3 : "'%".$v3."%'";
                $v3_1 = $is_int ? $v3 : "%".$v3."%";
                $is_empty_value = str_empty((string)$v3);
                $arr0 = [$columnsAttrDB[$k2], $o, $v3_1];
                $str0 = implode(' ', $arr0);
                $searchDB[$k1][$k2][] = $arr0;
                $fullLine = $columnsAttrDB[$k2].' '.$o.' '.$v3_1;
                $line = $columnsAttrDB[$k2].' '.$o.' ?';
                if(!$is_empty_value && !in_array($fullLine, $fullLines, true)) {
                    $searchSQL['lines'][] = $line;
                    $searchSQL['query'] .= (!empty($searchSQL['query']) ? ' OR ' : '');
                    $searchSQL['query'] .= $line;
                    $searchSQL['bindings'][] = $v3_1;
                    $fullLines[] = $fullLine;
                }
            }
            $prev_k2 = $k2;
        }
    }
    // all search
    $hasSearch = $hasSearchColumn || $hasSearchGlobal;

    $recordsTotal = 0;
    $recordsFiltered = 0;
    $recordsPaginate = 0;
    // $pageNum = 0;
    $row = [];

    $extra = [
        'client' => [
            'draw' => $draw,
            'order' => $order,
            'start' => $start,
            'length' => $length,
            'search' => $search,
            '_' => $_,

            'itemstart' => $itemStartClient,      // predicted item start based on client reqeust (start * length)
            'hasSearchColumn' => $hasSearchColumn,
            'hasSearchGlobal' => $hasSearchGlobal,
            'hasSearch' => $hasSearch,
        ],
        'col' => [
            'raw' => $columnsRaw,                 // raw columns data from request
            'type' => $columnsAttrType,           // data type of columns
            'formatter' => $columnsFormatter,     // columns value formatter/morph
            'dbblank' => $columnsDBBlank,         // empty db columns that is excluded in sql search
            'original' => $columnsOriginal,       // unique columns
            'clone' => $columnsClone,             // columns copying unique columns
            'all' => $columnsAll,                 // original and cloned columns

            'eval' => $columns,                   // evaluated columns data
            'frontend' => $columnsClient,         // structure for client processing
            'searchable' => $searchableColumns,   // searchable columns
            'search' => $searchAttr,              // attribute search
        ],

        'db' => [
            'attr' => $columnsAttrDB,             // columns attribute => db_col_name
            'col' => $columnsDB,                  // unique db columns
            'alias' => $columnsDBAlias,           // columns alias
            'search' => $searchDB,                // db column search
            'order' => $dbOrder,                  // db order
        ],

        'sql' => $searchSQL,

        'count' => [
            'unfiltered' => $recordsTotal,
            'filtered' => $recordsFiltered,
            'paginated' => $recordsPaginate,
        ],

        'row' => $row,                            // []
    ];

    return array_merge(compact(
        'draw',
        'order',
        // 'columns',          // evaluated columns data
        'start',
        'length',
        'search',
        '_',

        // added
        // 'pageNum',
    ), $extra);
}

/**
 * Renders data for frontend datatablejs
 *
 * - set `request.length` to `-1` to get all rows respective on the query
 * - doesn't support illegal `itemStart` that is not modulus zero to `pageLength`
 *
 * @param Request $request
 * @param array $columns the parsed columns definition
 * @param array|string|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query class or model
 * @param bool $withDataKey include array key pointer in data rows e.g. `[key=>value]`
 * @param bool $toJSON to JSON or array
 * @return array|\Illuminate\Http\JsonResponse
 */
function datatable_paginate(Request $request, array $columns, $query, bool $withDataKey = true, bool $toJSON = false) {

    /*  $columns OPTIONS:
        ----------------------------------------------------------------------------------------
        attr          (required) - column unique name
        db            (required) - db table column
        label         (required) - the display title in datatable
        db_fake       (disabled) - ???
        dt            (optional) - datatable sequence #
        class         (optional) - css classes
        sortable      (optional) - if column is sortable       (default: false)
        searchable    (optional) - if column is searchable     (default: false)
        type          (optional) - server-side column type     (default: "string")
        frontend_type (optional) - frontend column type        (default: "string")
                                   (date, num, num-fmt, html-num, html-num-fmt, html, string)
                                   https://datatables.net/reference/option/columns.type
        formatter     (optional) - formats value: function($value) { // your code }      (default: null)
        same_as       (optional) - copy the precedent column's characteristics           (default: "")
    */

    $is_success = false;
    $err_msg = '';
    $data = [];
    $columns2 = [];
    $countUnfiltered = 0;
    $countFiltered = 0;
    $pageDraw = 0;

    // in seconds
    $timeCountUnfiltered = 0;
    $timeCountFiltered = 0;
    $timeDataFiltered = 0;

    $request2 = $request;

    // function to get the query time in seconds
    $func_time = function(array $log) {
        // make sure you put `DB::enableQueryLog();` before the query line
        $time = (float)($log['time'] ?? 0);
        return round(($time > 0) ? ($time / 1000) : 0, 5);  // in seconds
    };

    // gets the sequenced array values from the collection
    $func_array_values_recursive = function(array $arr) {
        $o = [];
        foreach($arr as $k=>$v) {
            $o[$k] = array_values($v);
        }
        return $o;
    };

    // function to check if `table.col` exists
    $func_is_column_exist = function(string $tbl, string $col, array $tables_columns) {
        // dd($tbl.(!empty($tbl) ? '.' : '').$col);

        // return array_key_exists($tbl.(!empty($tbl) ? '.' : '').$col, $tables_columns);
        // dd($tables_columns);
        // dd(Arr::has($tables_columns, $tbl.(!empty($tbl) ? '.' : '').$col));
        return arr_has($tables_columns, $tbl.(!empty($tbl) ? '.' : '').$col);
    };

    if(is_string($query) && class_exists($query)) {
        $query = new $query();
    }

    DB::enableQueryLog();

    // intelligent getter of countable column. if failed, it assumes `id`
    $countable_id = (string)($columns[0]['db'] ?? 'id');
    $countable__ = (array)explode('.', $countable_id, 2);
    $countable_tbl = count($countable__) === 2 ? (string)($countable__[0] ?? '') : '';
    $countable_col = count($countable__) === 2 ? (string)($countable__[1] ?? '') : (string)($countable__[0] ?? '');

    // dump($countable_tbl);
    // dd(in_array($countable_col, $query->getConnection()->getSchemaBuilder()->getColumnListing($query->from), true));

    // get join tables, doesn't check its existence
    $join_tables = [];
    foreach((array)$query->joins as $k=>$v) {
        if(!empty($v->table) && !in_array($v->table, $join_tables, true)) {
            $join_tables[] = $v->table;
        }
    }
    $all_tables = $join_tables;
    array_unshift($all_tables, $query->from);

    // get columns list of the tables from the db
    $tables_columns = [];
    foreach($all_tables as $k=>$v) {
        try {
            foreach((array)($query?->getConnection()?->getSchemaBuilder()?->getColumnListing($v)) as $k2=>$v2) {
                $tables_columns[$v][$v2] = $k2;
            }
        } catch(\Exception $ex) { continue; }
    }

    // check if countable column is present
    // dump($countable_tbl);
    // dump($countable_col);
    // dump($tables_columns);
    // dd($func_is_column_exist($countable_tbl, $countable_col, $tables_columns));
    if(!(is_string($query->from) && !empty($query->from) && $func_is_column_exist($countable_tbl, $countable_col, $tables_columns))) {
        // dd(4242);
        throw new exception('Invalid countable column `'.$countable_id.'`');
        // goto point01;
    }

    // copy the main query builder object
    /** @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query */
    $query3 = $query2 = $query;

    $countUnfiltered = $query2->count($countable_id);

    if(!(is_object($query2) && (array_key_exists('Illuminate\Database\Eloquent\Model', class_parents($query2)) || method_exists($query2, 'get')))) {
        $err_msg = '$query has no method get()';
        // throw new exception($err_msg);
        goto point01;
    }

    // parse request data
    try {
        $pr = datatable_request_parse($request2, $columns);  // parsed request
    } catch(\Exception $ex) {
        $err_msg = $ex->getMessage();
        goto point01;
    }

    // $pr getter function
    $prget = function($key, string $type = '') use($pr) {
        if(!arr_has($pr, $key)) throw new exception('Key `'.$key.'` not found in $pr');
        $var = arr_get($pr, $key);
        if(!empty($type)) settype($var, $type);
        return $var;
    };

    $pageDraw = $prget('draw', 'int');
    $itemsLimit = $prget('length', 'int');
    $itemStart = $prget('start', 'int');

    $paginateCols = $prget('db.col', 'array');
    $colAlias = $prget('db.alias', 'array');
    $columns = $columns2 = $prget('col.eval', 'array');
    $SQLStr = $prget('sql.query', 'string');
    $SQLBindings = $prget('sql.bindings', 'array');
    $formatter = $prget('col.formatter', 'array');
    $dbblank = $prget('col.dbblank', 'array');
    $itemStartClient = $prget('client.itemstart', 'int');
    $d = null;

    if(!empty($SQLStr)) {
        $query3 = $query->whereRaw($SQLStr, $SQLBindings);
    }

    $countFiltered = $query3->count($countable_id);

    // change values according to client data
    $perPage = 0;
    $page = 0;
    if($itemsLimit === -1) {
        $itemStart = 0;  // set to 0 because its literally show all
        $itemsLimit = $countFiltered;
        $page = 1;
        $perPage = $countFiltered;
    }
    else if($itemsLimit === 0) {  // ok
        point01:  // portal of invalid $itemStart
        $itemStart = 0;
        $itemsLimit = 1; // set to 1 to override the default 15
        $page = 0;
        $perPage = 0;

        $query3 = $query3->whereRaw('FALSE = TRUE');
    }
    else if($itemsLimit > 0) {  // ok
        if($itemStart > $countFiltered) {  // ok
            goto point01;  // invalid itemStart index, return empty
        }
        // $itemStart = $itemStart;  // page number based from client data
        // $itemsLimit = $itemsLimit;
        $page = (int)ceil(($itemStart + 1) / $itemsLimit);
        $perPage = $itemsLimit;
    }

    // ???? fix laravel pagination plus one
    // $itemsLimit = $itemsLimit > 0 ? $itemsLimit - 1 : 0;

    // order
    foreach($pr['db']['order'] as $k=>$v) {
        $query3 = $query3->orderBy($k, $v);
    }

    // paginate
    try {
        $d = $query3->simplePaginate($itemsLimit, $colAlias, 'page', $page);
    } catch(\Exception $ex) {
        dd($ex);
    }

    // GET COUNT_UNFILTERED, COUNT_FILTERED, COUNT_PAGINATE
    $query_logs = DB::getQueryLog();

    DB::disableQueryLog();  // disable logging
    $timeCountUnfiltered = $func_time($query_logs[0]);
    $timeCountFiltered = $func_time($query_logs[1]);
    $timeDataFiltered = $func_time($query_logs[2]);
    $sqlCountUnfiltered = (string)($query_logs[0]['query'] ?? '');
    $sqlCountFiltered = (string)($query_logs[1]['query'] ?? '');
    $sqlDataFiltered = (string)($query_logs[2]['query'] ?? '');

    // get items
    $data_kv = obj_reflect($d->items(), true);
    $data_kv2 = [];

    // invoke formatter & include defaultContent|null
    foreach($data_kv as $k=>$v) {
        foreach($v as $k2=>$v2) {
            if(array_key_exists($k2, $formatter)) {
                $data_kv2[$k][$k2] = $formatter[$k2]($v2);
            } else {
                $data_kv2[$k][$k2] = $v2;
            }
        }
    }

    $data_v = $func_array_values_recursive($data_kv2);
    $data = $withDataKey ? $data_kv2 : $data_v;

    $countPaginate = count($data);
    $pr['count']['unfiltered'] = $countUnfiltered;
    $pr['count']['filtered'] = $countFiltered;
    $pr['count']['paginated'] = $countPaginate;
    $pr['row'] = $data;

    $pageQuotient = $perPage > 0 ? (($countFiltered > $perPage) ? ($countFiltered / $perPage) : 1) : 0;
    // $pageInt = (int)floor($pageQuotient);
    $pageInt = (int)ceil($pageQuotient);

    $pageMod = ($pageInt > 0) ? ($countFiltered % $perPage) : 0;
    $itemsLast = $pageMod <= 0 ? $perPage : $pageMod;
    $itemsLastDeficit = $itemsLast < $perPage ? $perPage - $itemsLast : 0;

    $onEachSide = $d->onEachSide;  // control how many additional links are displayed on each side of the current page within the middle, sliding window of links generated by the paginator - https://laravel.com/docs/8.x/pagination#adjusting-the-pagination-link-window
    $pageTotal = $pageInt;
    $pageCurrent = $countPaginate > 0 ? $d->currentPage() : 0;
    $pagingProgress_ = $pageTotal > 0 ? ($pageCurrent / $pageTotal) : 0;
    $pagingProgress = round($pagingProgress_, 5);
    $pagingRemaining = (1.0 - $pagingProgress);

    $hasItemsBehind = $pageCurrent > 1;
    $hasItemsOnward = $pageCurrent < $pageTotal;
    $pagesBehind = $pageCurrent <= 1 ? 0 : ($pageCurrent - 1);
    $pagesOnward = $pageTotal <= 0 ? 0 : ($pageTotal - $pageCurrent);

    $itemsFirst = ($countPaginate >= $perPage) ? $perPage : $countPaginate;
    $itemsCurrent = $countPaginate;

    $isPageFirst = $pageCurrent === 1;
    $isPageLast = $pageCurrent === $pageTotal;
    $isPageMiddle = $pageCurrent > 1 && !$isPageFirst && !$isPageLast;
    $isEmpty = $countPaginate === 0;
    $isFilled = !$isEmpty;
    $isPageOneOnly = $pageTotal === 1;
    $isPageLastDeficit = $itemsLastDeficit > 0;
    $isPageFilled = $itemsCurrent > 0 ;

    $itemsBehind = $pagesBehind <= 0 ? 0 : $perPage;
    $itemsOnward = $pagesOnward <= 0 ? 0 : ($pagesOnward > 1 ? $perPage : $itemsLast);
    $itemsBehindAll = $pagesBehind * $perPage;
    $itemsOnwardAll = ($pagesOnward > 0) ? (($perPage * ($pagesOnward - 1)) + $itemsLast) : 0;
    $itemsCurrentStart = $itemsBehindAll + ($isPageFilled ? 1 : 0);
    $itemsCurrentEnd = $itemsCurrentStart + ($isPageFilled ? $itemsCurrent - 1 : 0);

    $isClientItemStartExists = $itemStartClient <= $countFiltered;
    $isClientItemStartDisplayed = $isClientItemStartExists && ($itemStartClient >= $itemsCurrentStart && $itemStartClient <= $itemsCurrentEnd);

    // $pr['pageNum'] = $pageCurrent;

    // paging analytics
    $pr['analytics'] = compact(
        'onEachSide',
        'pageTotal',
        'pageCurrent',

        'pagesBehind',
        'pagesOnward',

        'pagingProgress',
        'pagingRemaining',

        'itemsFirst',
        'itemsLast',
        'itemsLastDeficit',
        'itemsCurrent',
        'itemsCurrentStart',
        'itemsCurrentEnd',
        'itemsBehind',
        'itemsOnward',
        'itemsBehindAll',
        'itemsOnwardAll',

        'isPageFilled',
        'isPageFirst',
        'isPageLast',
        'isPageMiddle',
        'isPageLastDeficit',
        'isClientItemStartExists',
        'isClientItemStartDisplayed',

        'isEmpty',
        'isFilled',

        'hasItemsBehind',
        'hasItemsOnward',

        'countUnfiltered',
        'countFiltered',
        'countPaginate',

        'timeCountUnfiltered',
        'timeCountFiltered',
        'timeDataFiltered',

        'sqlCountUnfiltered',
        'sqlCountFiltered',
        'sqlDataFiltered',
        // '',
    );


    $is_success = true;

    point1:
    $arr = [
        "success" => $is_success,
        "draw" => $pageDraw,
        "data" => $data,
        // "columns" => $pr['col']['frontend'],
        "recordsTotal" => $countUnfiltered,
        "recordsFiltered" => $countFiltered,
        "request" => $pr['client'],
        "analytics" => $pr['analytics'],
    ];

    return $toJSON ? response()->json($arr) : $arr;
}











/** -----------------------------------------------
 * DATABASE
 */

/**
 * Get the model's table name
 *
 * @param string $class
 * @return string
 */
function db_model_table_name(string $class)
{
    if(!class_exists($class))
        throw new exception('Invalid class: '.$class);
    $parent = 'Illuminate\Database\Eloquent\Model';
    if(!array_key_exists($parent, class_parents($class)))
        throw new exception('Missing class\' parent `'.$parent.'` of the given `'.$class.'`');
    return $class::__callStatic('getTable', []);
}

/**
 * Gets the relation info
 *
 * - do not forget to typehint the `$q` in the closure as stated in `@usage` below.
 * - when accessing the parent, use the template below, although you will cause an additional db query.
 *
 * @usage closure variable typehint: `/** @var \Illuminate\Database\Query\Builder $q *\/ list($t, $p) = db_relation_info($q);`
 *
 * @usage access parent template: `$q->join($p, $p.'.id', '=', $t.'.foreign_id'); $q->select([$p.'.id as parent_foreign_id', $t.'.*']);`
 *
 * @param \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Query\Builder $query
 * @return <int,string>
 */
function db_relation_info($query)
{
    $table = '';
    $tableParent = '';
    try {
        $table = trim((string)$query->getQuery()->getModel()->getTable());
        $tableParent = trim((string)$query->getParent()->getTable());
    } catch(\Exception $ex) {}
    if(empty($table)) throw new exception('$table is empty');
    if(empty($tableParent)) throw new exception('$tableParent is empty');
    return [$table, $tableParent];
}

function db_eloquent_relations(bool $withNamespace = true)
{
	$arr = [
		'\Illuminate\Database\Eloquent\Relations\BelongsTo',
		'\Illuminate\Database\Eloquent\Relations\BelongsToMany',
		'\Illuminate\Database\Eloquent\Relations\HasMany',
		'\Illuminate\Database\Eloquent\Relations\HasManyThrough',
		'\Illuminate\Database\Eloquent\Relations\HasOne',
		'\Illuminate\Database\Eloquent\Relations\HasOneOrMany',
		'\Illuminate\Database\Eloquent\Relations\HasOneThrough',
		'\Illuminate\Database\Eloquent\Relations\MorphMany',
		'\Illuminate\Database\Eloquent\Relations\MorphOne',
		'\Illuminate\Database\Eloquent\Relations\MorphOneOrMany',
		'\Illuminate\Database\Eloquent\Relations\MorphPivot',
		'\Illuminate\Database\Eloquent\Relations\MorphTo',
		'\Illuminate\Database\Eloquent\Relations\MorphToMany',
	];
	$arr2 = [];
	foreach($arr as $k=>$v) {
		$arr2[] = !$withNamespace ? basename($v) : $v;
	}
	return $arr2;
}

/**
 * Gets the array of `connection` orWith `table`
 *
 * @param string|array<int,string> $conn_tbl
 * @return array<int,string>
 * @throws Exception
 */
function db_param($conn_tbl, bool $strict = false) {
    if(!in_array(gettype($conn_tbl), ['string', 'array']) || empty($conn_tbl))
        throw new exception('Must be a filled string or array: $conn_tbl');

    $bool1 = is_array($conn_tbl) && count($conn_tbl)===2;
    $conn = (string)($bool1 ? $conn_tbl[0] : config_get('env.APP_CONNECTION', '', 'string'));
    $table = ($bool1 ? $conn_tbl[1] : (is_array($conn_tbl) ? $conn_tbl[1] : $conn_tbl));
    $db_name = '';
    try {
        $db_name = DB::connection($conn)->getDatabaseName();
    } catch(\Exception $ex) {}
    // $ct = [$conn, $table, $db_name];

    if($strict) {
        if(str_empty($conn)) throw new exception('Connection name is empty');
        if(str_empty($table)) throw new exception('Table name is empty');
        if(str_empty($db_name)) throw new exception('Database name is empty');

        if(Schema::connection($conn)->hasTable($table) !== true)  // check table if exists
            throw new exception('Table not found: '.$table);
    }

    return [$conn, $table, $db_name];
}

/**
 * Checks the database `connection` and/or `table`
 *
 * @param string $connection
 * @param string $table
 * @return array{0:bool,1:string}
 */
function db_check(string $connection, string $table = '') {
    $output = [false, ''];
    $table = trim(preg_replace('/\s+/u', ' ', $table));
    try {
        // check empty string
        if(str_empty($connection) === true)
            throw new exception('Empty $connection');

        // check connection
        try {
            /** @var \Illuminate\Database\Connection $c */
            $c = DB::connection($connection);
            $c->getPdo();
            if(!$c->getDatabaseName()){
                throw new exception('Invalid DB name');
            }

        } catch(\Exception $ex2) {
            throw new exception('Invalid connection: '.$connection.'');
        }

        // check table
        if(!empty($table)) {
            if(!Schema::connection($connection)->hasTable($table))
                throw new exception('Invalid table: '.$table.'');
        }

        $output[0] = true;
    } catch(\Exception $ex) {
        $output[1] = $ex->getMessage();
    }
    point1:
    return $output;
}

/**
 * Gets the `connection` orWith `table` object
 *
 * @param string|array<int,string> $conn_tbl
 * @return \Illuminate\Database\Query\Builder
 * @throws Exception
 */
function db_obj($conn_tbl) {
    $dbop = db_param($conn_tbl);
    $connection = $dbop[0];
    $table = $dbop[1];
    $conn_check = db_check($connection, $table);
    if($conn_check[0] !== true)
        throw new Exception($conn_check[1]);
    return DB::connection($connection)->table($table);
}

/**
 * Get or insert cache
 *
 * - only works for table prefixes: `ad_, pl_` - atomic data, preload
 * - `pl_` structure: `id, {some_columns}, created_at, is_official, is_valid`
 * - `ad_` structure: `id, {some_columns}, created_at, is_valid`
 * - doesn't care about `is_valid` since it's a caching approach
 * - doesn't sanitize the value
 * - only single row per call
 * - set 3rd param to true ONLY IF you're sure if its case-insensitive and there are no diacritics involved
 *
 * @param string|array<int,string> $conn_tbl
 * @param array $needles
 * @param boolean $case_sensitive
 * @return int
 * @throws Exception
 */
function db_cache_fetsert_id($conn_tbl, array $needles, bool $case_sensitive = true)
{
    $ct = db_param($conn_tbl, true);  // can throw exception
    $conn = $ct[0];
    $table = $ct[1];
    $db = $ct[2];

    // $needles_orig = $needles;
    $dt_now_str = dt_now_str(dt_standard_format(), 'UTC');
    $attr = [];
    $values = [];
    $where = [];
    $prefixes = ['ad_', 'pl_'];
    $word = '';

    // remove id key
    if(array_key_exists('id', $needles))
        unset($needles['id']);

    // strip `is_` and `_at`
    foreach($needles as $k=>$v) {
        if(Str::startsWith($k, 'is_') || Str::endsWith($k, '_at'))
            continue;
        $where[$k] = $v;
    }
    $attr = $where;

    // check if $where is empty
    if(empty($where))
        throw new exception('$needles is empty');  // it needs some element without prefixes (ad_, pl_)

    // logic
    if(Str::startsWith($table, $prefixes[0])) {  // atomic data
        $word = 'Atomic data';
        $values = ['is_valid'=>1];
    }
    else if(Str::startsWith($table, $prefixes[1])) {  // preloads
        $word = 'Preload';
        $values = ['is_official'=>0, 'is_valid'=>1];
        if(array_key_exists('is_official', $needles))
            $values['is_official'] = (int)$needles['is_official'];
    }
    else {
        throw new exception('Table `'.$table.'` doesn\'t starts with required prefixes');
    }

    // upsert
    $id = 0;
    $data = [];
    $hasNull = false;
    DB::beginTransaction();
    try {
        $obj = DB::connection($conn)->table($table);
        $where_db = $obj;
        foreach($where as $k=>$v) {
            if(!$hasNull && (is_null($v) || $v === '')) {
                $hasNull = true;
                goto point1;
            }
            if($case_sensitive)
                $where_db->whereRaw('BINARY `'.$k.'` = ? ', [$v], 'and');
            else
                $where_db->where($k, '=', $v, 'and');
        }
        if($hasNull) {
            goto point1;
        }
        $where_db->lockForUpdate();

        if($where_db->count() > 0) {
            $data = obj_recode($obj->where($where)->first(), true);
        } else {
            $merged = array_merge($attr, $values, ['created_at'=>$dt_now_str]);
            $obj->insert($merged);
            if($obj->where($where)->count() < 1)
                throw new exception('Failed to insert: '.strtolower($word));
            $data = obj_recode($obj->where($where)->first(), true);
        }

        if(empty($data))
            throw new exception('Empty data');
        if(!array_key_exists('id', $data))
            throw new exception('Inexistent key: id');
        if(!is_int($data['id']))
            throw new exception('Value of `id` is not integer');
        $id = (int)$data['id'];

        point1:
        DB::commit();
    } catch(\Exception $ex) {
        DB::rollBack();
    }

    // if($id <= 0)
    //     throw new exception('Value of `id` must be UNSIGNED');

    // point1:
    return $id;
}


function db_stored_procedure(string $conn, string $func_name, array $binding=[], bool $to_array=false)
{
	// $FR_app = FieldRules::getGeneral();
	$kw = 'CALL ';  // keyword starts with, with 1 space
	$func_name = str_sanitize($func_name);
	// if(str_preg_match($FR_app['function']['regex'], $func_name))
	//     throw new exception('`$func_name` must be a valid function name');
	$param = '';
	$x = -1;
	foreach($binding as $key=>$val) {
		$param .= ((++$x)>0 ? ',' : '').'?';
	}
	$sql = $kw.$func_name.'('.$param.')';
	$select = DB::connection($conn)->select($sql, $binding);
	$output = $to_array ? obj_reflect($select) : $select;
	return $output;
}

function db_sql_with_binding($builder)
{
    $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
    $query = vsprintf($query, $builder->getBindings());
    return $query;
}

/**
 * Update or Insert to database
 *
 * @param string|array<int, string> $conn_tbl
 * @param array $attr
 * @param array $values
 * @return array<int,bool|int|array<int,mixed>>
 */
function db_upsert($conn_tbl, array $attr, array $values)
{
    $conn = db_obj($conn_tbl);
    $merged = array_merge($values, $attr);
    $count1 = $conn->where($attr)->count();

    $bool1 = $conn->updateOrInsert($attr, $values);

    $data2 = $conn->where($merged)->get()->toArr();
    $count2 = count($data2);

    $success = $bool1 || $count2 > 0 || $count2 > $count1;
    $count2 = $count2 > 0 ? $count2 : 0;

    return [$success, $count2, $data2];
}











/** -----------------------------------------------
 * DOCUMENTATION
 */

/**
 * Parses PHPDocs string lines
 *
 * @param \ReflectionMethod $obj
 * @return <string,array{title:string,desc:<int,string>,at:<string,string>,param:array{position:int,name:string,type:\ReflectionType|null,default:mixed}}>
 */
function docu_parse($obj): array {
    $flags = [false, false, false];
    $title = '';
    $desc = [];
    $ats = [];
    $str = $obj->getDocComment();
    if(!is_string($str)) goto point1;

    $pcs1 = explode("\n", $str);
    foreach($pcs1 as $k=>$v) {
        $v = Str::of($v)->trim()->__toString();  // trim

        if(Str::contains($v, ['/**', '*/']))  // skip start and end symbols
            continue;

        if(Str::startsWith($v, '*'))  // trim again and strip " * "
            $v = Str::of(Str::replaceFirst('*', '', $v))->trim()->__toString();

        if(empty($v))  // skip empty
            continue;

        if(!$flags[0]) {
            if(!empty($v))
                $title = $v;
            $flags[0] = true;
        }
        elseif($flags[0]) {
            if(Str::startsWith($v, '@')) {
                $pcs2 = explode(' ', Str::replaceFirst('@', '', $v), 2);
                if(count($pcs2) !== 2) continue;
                $pcs2 = [trim($pcs2[0]), trim($pcs2[1])];
                if(empty($pcs2[0]) || empty($pcs2[1])) continue;
                $ats[$pcs2[0]] = $pcs2[1];
                $flags[2] = true;
            } else {
                $desc[] = $v;
                $flags[1] = true;
            }
        }
    }
    point1:
    $param = [];
    foreach($obj->getParameters() as $k=>$v) {
        $pm = [];
        try {
            $pm['position'] = $v->getPosition();
            $pm['name'] = $v->getName();
            $pm['type'] = $v->getType();
            $pm['default'] = $v->getDefaultValue();
        } catch(\Exception $ex) {}
        $param[] = $pm;
    }
    return [
        'title' => $title,
        'desc' => $desc,
        'at' => $ats,
        'param' => $param,
    ];
}

/**
 * Gets the sanitized data type (in string)
 *
 * @param null|array $arr
 * @return string
 */
function docu_type_sanitize($var)
{
    // $t = '';
    if(is_null($var) || $var === 'void' || !is_array($var) || !array_key_exists('type', $var))
        goto point1;
    // $type = $var['type'];

    // throw new exception('$type must be an instance of \ReflectionNamedType');
    if(is_array($var) && array_key_exists('type', $var)) {
        if(($var['type'] instanceof \ReflectionNamedType) && method_exists($var['type'], 'getName')) {
            $var = $var['type']->getName();
        } else {
            $var = '';
        }
    }

    point1:
    if(in_array($var, ['Closure']) || Str::startsWith($var, ['Illuminate\\'])) {
        $var = '\\'.$var;
    }
    // else
    //     $t = $t;
    return $var;
}


/**
 * Get the PHPDoc string of a class (public only)
 *
 * - merges duplicate methods
 *
 * @param array|string $class
 * @return string
 */
function docu_string($class, bool $echoAndDie = false)
{
    $space = $echoAndDie ? '&nbsp;' : ' ';

    if(!(is_array($class) || is_string($class)))
        throw new exception('$class must be array or string');
    $class = is_string($class) ? [$class] : $class;
    $public = [];
    $private = [];
    $out = '';
    foreach($class as $k1=>$v1) {
        $v1 = trim($v1);
        if(!class_exists($v1))
            throw new exception('Class doesn\'t exists: '.$v1);
        $ref = new \ReflectionClass($v1);
        foreach($ref->getMethods() as $k=>$v) {
            if($v->getModifiers() === ReflectionMethod::IS_PUBLIC) {
                $public[$v->getName()] = docu_parse($v);
            }
        }
    }
    foreach($public as $k=>$v) {
        $ret = $v['at']['return'] ?? '';
        $ret = (!empty($ret) && $ret !== 'void') ? docu_type_sanitize($ret) : 'void';
        $method = !empty($k) ? $k : '';
        $title = !empty($v['title']) ? ' '.$v['title'] : '';
        $param = '';
        foreach($v['param'] as $k2=>$v2) {
            $t = docu_type_sanitize($v2);
            $param .= (
                ($k2 > 0 ? ', ' : '')  // separator
                .$t // type
                .(!empty($t) ? ' ' : '').'$'.$v2['name']  // var_name
                .(array_key_exists('default', $v2) ? ' = '.var_stringify($v2['default']) : '')  // default value
            );
        }
        if(!empty($method))
            $out .= $space.'* @method static '.$ret.' '.$method.'('.$param.')'.$title."\n";
    }
    if(!empty($out))
        $out = '/**'."\n".$out.$space.'*/';
    if($echoAndDie) {
        echo nl2br($out);
        die('');
    }
    return($out);
}

























/** -----------------------------------------------
 * DATE TIME
 */

/**
 * Get the standard datetime format
 *
 * @return string
 */
function dt_standard_format()
{
    return 'Y-m-d H:i:s.u';
}

/**
 * Use this function to properly validate carbon object
 *
 * @param \Carbon\Carbon|null $obj
 * @return bool
 */
function dt_is_carbon($obj)
{
    // return (!is_null($obj) && ($obj instanceof Carbon) && (get_class($obj) === 'Carbon\Carbon'));
    return (!is_null($obj) && is_object($obj) && (get_class($obj) === 'Carbon\Carbon' || array_key_exists('Carbon\Carbon', class_parents($obj))));
}

/**
 * Advanced datetime parse function
 *
 * @property bool $is_valid
 * @param string $dt_str
 * @param array|string $dt_format [ from, to ] | from
 * @param array|string $tz [ from, to ] | from
 * @return \App\Traits\DT
 * @uses \Carbon\Carbon
 */
function dt_parse(string $dt_str, $dt_format = ['', ''], array $tz = ['', ''])
{
    $tz = [ empty($tz[0]) ? 'UTC' : $tz[0], empty($tz[1]) ? 'UTC' : $tz[1] ];
    $validate_array = function(array $arr) {
        return (
            arr_depth($arr) === 1
            && arr_type($arr) === 'sequential'
            && count($arr) === 2
            && is_string($arr[0])
            && is_string($arr[1])
            && !str_empty($arr[0])
            && !str_empty($arr[1])
        );
    };
    $arr_func1 = function(array $arr, $key) {
        return (object)['from'=>$arr[$key][0] ?? null, 'onto'=>$arr[$key][1] ?? null];
    };
    $dtf = dt_format_eval($dt_format);
    if(!$validate_array($tz)) throw new Exception('Invalid array structure `$tz`');
    $format_fm = $dtf[0];
    $format_to = $dtf[1];
    $tz_fm = $tz[0];
    $tz_to = $tz[1];
    $dt = $output = [false, [], [], [], []];
    $dt_ = null;
    try { $dt_ = Carbon::createFromFormat($format_fm, $dt_str, $tz_fm); } catch (\Exception $ex) {}
    if(!dt_is_carbon($dt_)) goto point1;
    $dt2 = $dt_->clone()->setTimeZone($tz_to);
    if(!dt_is_carbon($dt2)) goto point1;
    $str_fm = $dt_->format($format_fm);
    $str_to = $dt2->format($format_to);
    $output[0] = true;
    $output[1] = [$format_fm, $format_to];
    $output[2] = [$tz_fm, $tz_to];
    $output[3] = [$dt_, $dt2];
    $output[4] = [$str_fm, $str_to];

    $dt = $output;
    point1:
    return (object)[
        'is_valid'  => $dt[0],
        'timezone'  => $arr_func1($dt, 2),
        'format'    => $arr_func1($dt, 1),
        'carbon'    => $arr_func1($dt, 3),
        'string'    => $arr_func1($dt, 4),
    ];
}

/**
 * Parses datetime string if valid
 *
 * @param string $dt_str
 * @param array|string $dt_format [from, onto] | from
 * @param array|string $tz [from, onto] | from
 * @return string onto
 */
function dt_parse_str(string $dt_str, $dt_format, $tz)
{
    return dt_parse($dt_str, $dt_format, $tz)->string->onto ?? '';
}

/**
 * Validates date format and mutates the output to array(from, to). String values.
 *
 * @param array|string $dt_format
 * @param boolean $strict_mode
 * @return array [from, to]
 * @throws Exception
 */
function dt_format_eval($dt_format, bool $strict_mode=false)
{
    $output = ['', ''];

    $evaluator = function(string $dt_format) use($strict_mode) {
        $output = [false, '', ''];
        try {
            if($strict_mode === true && str_empty($dt_format))
                throw new Exception('`$dt_format` must be a filled string');
            $fm1 = str_empty($dt_format) ? dt_standard_format() : $dt_format;
            if(!str_empty($fm1))
                $output[2] = $fm1;
            $output[0] = true;
        } catch(Exception $ex) {
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
        if(arr_depth($dt_format) !== 1)
            throw new Exception('`$dt_format` array must have 1 depth');
        if(!arr_type_seq($dt_format))
            throw new Exception('`$dt_format` must be sequential array');
        if(!in_array($count, [1, 2]))
            throw new Exception('`$dt_format` must have 1 or 2 elements');
        if(!is_string($dt_format[0]))
            throw new Exception('`$dt_format[0]` must be string');

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
            $output[1] = !str_empty($to1) ? $to2 : $output[0];
        }
    }
    else {
        throw new Exception('`$dt_format` must be string or array');
    }
    return $output;
}

function dt_tz_offset_hours(string $tz)
{
    return Carbon::now($tz)->getOffsetString();
}

function dt_now(string $tz = 'UTC')
{
    return Carbon::now($tz);
}

function dt_now_str(string $format, string $tz = 'UTC')
{
    if(empty(trim($format))) $format = dt_standard_format();
    return dt_now($tz)->format($format);
}

function dt_now_user()
{
    return dt_now(config('user.settings.timezone', env('APP_TIMEZONE_DEFAULT')));
}

function dt_now_user_str(string $format)
{
    if(empty(trim($format))) $format = dt_standard_format();
    return dt_now_user()->format($format);
}

function dt_copyright_str(bool $withFrom = false) {
    $tz_user = config('user.settings.timezone');
    $dt_standard_format = dt_standard_format();

    $created_at_str = config('env.APP_CREATED_AT');
    $created_at = dt_parse($created_at_str, ['', ''], [$tz_user, $tz_user])->carbon->onto ?? null;

    $dt_now_user = dt_now($tz_user);
    $dt_now_user_str = $dt_now_user->format($dt_standard_format);

    $from = dt_is_carbon($created_at) ? $created_at->format('Y') : '';
    $onto = dt_is_carbon($dt_now_user) ? $dt_now_user->format('Y') : '';

    $cr_year = !$withFrom ? $onto : (version_compare($onto, $from, '>') ? $from.'-'.$onto : $onto);
    return $cr_year;
}

/**
 * Evaluates two datetime string (in ascending) and returns the first correct value.
 *
 * @param string $dt_format
 * @param string $dt_val
 * @param string $dt_fallback
 * @return string
 */
function dt_eval_str(string $dt_format, string $dt_val, string $dt_fallback = '') {
    $output = '';
    $dt_format = str_empty($dt_format) ? dt_standard_format() : $dt_format;
    $dtf = dt_format_eval($dt_format);
    $dt1 = dt_parse_str($dt_val, $dtf, ['UTC', 'UTC']);
    $dt2 = dt_parse_str($dt_fallback, $dtf, ['UTC', 'UTC']);
    $bool1 = str_filled($dt1);
    $bool2 = str_filled($dt2);
    if($bool1 !== true && $bool2 !== true)
        goto point1;
    if($bool1 === true)
        $output = $dt1;
    else if($bool2 === true)
        $output = $dt2;
    point1:
    return $output;
}

function dt_standard_tz()
{
    return 'UTC';
}

function dt_is_timezone(string $tz_str) {
    // if returns `null`, the error is either on `dt_format` or `dt_str`, or BOTH
    $dt_now = null;
    $dt_test = null;
    $dt_format = dt_standard_format();
    try {
        $dt_now = Carbon::now(dt_standard_tz())->startOfDay()->format($dt_format);
        $dt_test = Carbon::createFromFormat($dt_format, $dt_now, $tz_str);
        return true;
    } catch (\Exception $ex) {
        return false;
    }
}

function dt_server_tz() {
    $stz = config('app.timezone', dt_standard_tz());
    if(!dt_is_timezone($stz)) throw new exception('Invalid server timezone');
    return $stz;
}

function dt_translate_unique(string $dt_str) {
    // -- translates dt_str to a unique number for ordering
    // -- ONLY ACCEPTED FORMAT (Y-m-d H:i:s.u)
    // -- does not modify the timezone
    // -- returns empty array if there's an error on `dt_str` format
    // -- accepted range: (0000-01-01 00:00:00.000000 - 9999-12-31 23:59:59.999999) + parse validation
    // max characters

    $output = [];
    $dt_format = dt_standard_format();
    $tz_format = dt_standard_tz();
    $err_msg = '';
    $dt_test = dt_parse($dt_str, [$dt_format, $dt_format], [$tz_format, $tz_format]);
    $dts = (string)($dt_test->string->onto ?? '');
    // $long = Str::replace([' ', '-', ':', '.'], '', $dt_str);

    $opt = [
        'raw' => $dt_str,

        'year' => '',
        'month' => '',
        'day' => '',
        'hour' => '',
        'minute' => '',
        'second' => '',
        'ms' => '',

        'timestamp' => 0,
        'timestamp_pad' => '',

        'ms_decimal' => 0.0,
        'ms_round' => 0,
        'month_day' => 0,
        'month_day_pad' => '',
        'hms' => 0,
        'hms_pad' => '',

        'u' => 0,
        'u14' => '',

        // 'long' => '',
        // 'short' => '',  // aka u13
    ];

    // splice and pad zeroes
    $fn_splice = function(int $index, int $offset) use($dts) {
        return str_pad(substr($dts, $index, $offset), $offset, '0', STR_PAD_LEFT);
    };

    try {
        // validate datetime range
        if(!validate_datetime($dt_str))
            throw new exception('Datetime string does not match the format.');

        // try parse datetime string
        if(!$dt_test->is_valid)
            throw new exception('Failed to parse datetime string.');



        $dt_new = $dt_test->carbon->onto->clone();
        $opt['timestamp'] = $dt_new->timestamp;
        $opt['timestamp_pad'] = str_pad((string)$opt['timestamp'], 10, '0', STR_PAD_RIGHT);

        $opt['year'] = $fn_splice(0, 4);
        $opt['month'] = $fn_splice(5, 2);
        $opt['day'] = $fn_splice(8, 2);
        $opt['hour'] = $fn_splice(11, 2);
        $opt['minute'] = $fn_splice(14, 2);
        $opt['second'] = $fn_splice(17, 2);
        $opt['ms'] = $fn_splice(20, 6);

        $ms_ = substr($opt['ms'], 0, 2);
        $ms_2 = (float)(((int)$ms_[0]).'.'.((int)$ms_[1]));
        $opt['ms_decimal'] = $ms_2;
        $opt['ms_round'] = (int)round($ms_2);


        // $part1 = $opt['year'];  // year
        // $part2 = '';  // month_day
        // $part3 = '';  // hour_minute_second
        // $part4 = '';  // rounded microsecond
        // $all = '';

        $month_day = ((31 * (int)$opt['month']) + (int)$opt['day']);
        $month_day_ = str_pad((string)$month_day, 3, '0', STR_PAD_RIGHT);
        $opt['month_day'] = $month_day;
        $opt['month_day_pad'] = $month_day_;

        $hms = ((int)$opt['hour'] * 60 * 60) + ((int)$opt['minute'] * 60) + ((int)$opt['second']);
        $hms_ = str_pad((string)$hms, 5, '0', STR_PAD_RIGHT);
        $opt['hms'] = $hms;
        $opt['hms_pad'] = $hms_;

        $opt['u'] = ($opt['year'].$opt['month_day_pad'].$opt['hms_pad'].$opt['ms_round']);



        // $s2 = (15 * 60 * 60) + (10 * 60) + (59);

        // $opt['short'] = substr($long, 2, 2).substr($long, 4, 2).substr($long, 6, 2).substr($long, 8, 2).substr($long, 10, 2).substr($long, 12, 2).substr($long, 14, 1);



    } catch(\Exception $ex) {
        $err_msg = $ex->getMessage();
    }

    point1:
    return $output;
}

/**
 * Gets the location in a date range
 *
 * @param string $start_at
 * @param string $end_at
 * @param Carbon $date_at
 * @param array $txt `['lesser', 'greater', 'within']` the output text representation
 * @return string output text
 */
function dt_range_location(string $start_at, string $end_at, Carbon $date_at, array $txt = ['lesser', 'greater', 'within'])
{
    $opt = '';
    try {
        // strip time if there is
        $len = strlen($date_at->format('Y-m-d'));
        $start_at = strlen($start_at) > $len ? substr($start_at, 0, $len) : $start_at;
        $end_at = strlen($end_at) > $len ? substr($end_at, 0, $len) : $end_at;

        $start_at_ = Carbon::createFromFormat('Y-m-d', $start_at, config('app.app_timezone'))->startOfDay();
        $end_at_ = Carbon::createFromFormat('Y-m-d', $end_at, config('app.app_timezone'))->endOfDay();

        if($start_at_ > $end_at) throw new exception('Invalid range');

        if($date_at < $start_at_) {
            $opt = $txt[0];
        }
        else if($date_at > $end_at_) {
            $opt = $txt[1];
        }
        else if($date_at >= $start_at_ && $date_at <= $end_at_) {
            $opt = $txt[2];
        }
    } catch(\Exception $ex) { }
    return $opt;
}













/** -----------------------------------------------
 * EXCEPTION
 */

/**
 * Throws a readable JSON Exception from a variable
 *
 * @param mixed $var
 * @param boolean $assoc
 * @return mixed
 */
function exception_json($var, bool $assoc = true)
{
    throw new CustomJSONException($var, 0, null, $assoc);
}













/** -----------------------------------------------
 * JSON
 */

/**
 * JSON Echo & Die
 *
 * @param mixed $var
 * @param bool $withTrace
 * @return void
 */
function json_echo_and_die($var, bool $withTrace = false)
{
    if($withTrace) {
        $t = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1] ?? [];
        echo json_encode(['var' => $var, 'trace' => $t]);
    }
    else echo json_encode($var);
    die();
}













/** -----------------------------------------------
 * LARAVEL BACKPACK
 */

/**
 * Get the int value of `backpack_auth()->id()`
 *
 * @return int
 */
function lbp_auth_id()
{
    return (int)backpack_auth()->id();
}

/**
 * Get the int value of `backpack_auth()->id()`
 *
 * @return mixed
 */
function lbp_request_get(string $key)
{
    return app(BH_CRUD_KEY)->getRequest()->get($key);
}
















/** -----------------------------------------------
 * OBJECT
 */

/**
 * Encodes then decodes array. Useful for casting implicit objects.
 *
 * @param mixed $var
 * @param boolean $assoc
 * @return array|object
 */
function obj_recode($var, bool $assoc = false)
{
    return json_decode(json_encode($var), $assoc);
}

/**
 * Converts protected/unprotected objects to object/array
 *
 * @param mixed $obj
 * @param boolean $to_array
 * @param mixed $default
 * @return mixed|object|array
 */
function obj_reflect($obj, bool $to_array = false, $default = null)
{
    $reflector = function($obj1, bool $to_array = false) {
        $retval = [];
        if(!(is_object($obj1) || is_array($obj1)))
            throw new Exception('$obj must be an object or array');
        if(is_array($obj1)) {
            $retval = obj_recode($obj1, true);
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
        $retval = !empty($obt) ? ($to_array ? obj_recode($obt, true) : $obt) : $retval;
        point1:
        return $retval;
    };
    $reflected = $reflector($obj, $to_array) ?? $default;
    return $reflected;
}
















/** -----------------------------------------------
 * REQUEST
 */

/**
 * Redirect the user no matter what. No need to use a return
 * statement. Also avoids the trap put in place by the Blade Compiler.
 *
 * @param string $url
 * @param int $code http code for the redirect (should be 302 or 301)
 */
function redirect_now($url, $code = 302)
{
    try {
        App::abort($code, '', ['Location' => $url]);
    } catch (\Exception $exception) {
        // the blade compiler catches exceptions and rethrows them
        // as ErrorExceptions :(
        //
        // also the __toString() magic method cannot throw exceptions
        // in that case also we need to manually call the exception
        // handler
        $previousErrorHandler = set_exception_handler(function () { });
        restore_error_handler();
        call_user_func($previousErrorHandler, $exception);
        die;
    }
}
















/** -----------------------------------------------
 * REQUEST
 */

/**
 * Gets the rules of FormRequest class
 *
 * - callbacks not included
 */
function request_rules(string $requestClass)
{
    if(!class_exists($requestClass))
        throw new exception('Non-existent class: '.$requestClass);
    if(!array_key_exists(Request::class, class_parents($requestClass)))
        throw new Exception('$requestClass must a parent class '.Request::class);
    $onlyRules = [
        'min' => 'int',
        'max' => 'int',
        'regex' => 'string',
        'date_min' => 'string',
        'date_max' => 'string'
    ];
    /** @var \Rguj\Laracore\Request\Request $c */
    $c = resolve($requestClass);
    $r = $c->rules();
    $g = $c->genericRule ?? [];
    $o = [];

    // format rules
    foreach($r as $k1=>$v1) {
        foreach($v1 as $k2=>$v2) {
            if(is_string($v2)) {
                list($rule, $ruleVal) = explode(':', (Str::contains($v2, ':') ? $v2 : $v2.':'), 2);
                if(!empty($rule) && !empty($ruleVal) && array_key_exists($rule, $onlyRules)) {
                    $o[$k1][$rule] = var_cast($ruleVal, $onlyRules[$rule]);
                }
            }
        }
    }

    // add missing generic rule
    foreach($g as $k2=>$v2) {
        foreach($v2 as $k3=>$v3) {
            if(arr_has($o, $k2) && !arr_has($o, $k2.'.'.$k3)) {
                arr_set($o, $k2.'.'.$k3, $v3);
            }
        }
    }

    return $o;
}











/** -----------------------------------------------
 * ROUTE
 */

function route_parse_url(string $url, bool $adjustScheme = true)
{
	// return url_parse($url, $adjustScheme);
	return url_parse($url);
}


/**
 * Generates route that returns view
 *
 * @param string $uri
 * @param string $view
 * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|null
 */
function route_generate_view(string $uri, string $view)
{
    $pcs1 = explode('/', $uri);
    $name = str_only_alphanumus(trim(!empty($pcs1) ? $pcs1[count($pcs1)-1] : ''));
    Route::get($uri, function() use($view) { return view($view); })->name($name.'.view');
}

/**
 * Generates routes with a single line
 *
 * @param string $uri
 * @param string $class
 * @param array $options (optional)
 * @return \Illuminate\Routing\Route|null
 * @throws Exception
 */
function route_generate_resource(string $uri, string $class, array $options = [])
{
    /*
        Verb	        Path	            Action	    Route Name
        GET	            /photo	            index	    photo.index
        GET	            /photo/create	    create	    photo.create
        POST	        /photo	            store	    photo.store
        GET	            /photo/{photo}	    show	    photo.show
        GET	            /photo/{photo}/edit	edit	    photo.edit
        PUT/PATCH	    /photo/{photo}  	update	    photo.update
        DELETE  	    /photo/{photo}      destroy	    photo.destroy
    */
    $uri = trim($uri);
    $uri = substr($uri, 0, 1) !== '/' ? '/'.$uri : $uri;
    $name = '';
    $naming_scheme = 2;
    $route_name_index = 'index';

    if($naming_scheme === 1) {
        $name = substr($uri, 0, 1) === '/' ? ltrim($uri, '/') : $uri;
        $name = Str::replace('/', '_', $name);
    }
    else if($naming_scheme === 2) {
        $pcs1 = explode('/', $uri);
        $name = trim(!empty($pcs1) ? $pcs1[count($pcs1)-1] : '');
    }
    else {
        throw new Exception('Invalid naming scheme');
    }

    if($name === $route_name_index)
        throw new Exception('Route name prefix `'.$route_name_index.'` is reserved');
    // if(empty($name)) throw new Exception('Invalid route name');
    if(empty($name)) $name = $route_name_index;
    $name = str_only_alphanumus($name);

    // start logic
    $req_types = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
    $f1 = function($val_) { return  'Invalid request type: '.$val_; };
    $c1 = count($req_types);
    $options2 = [];
    $options = empty($options) ? $req_types : $options;

    // digitify all options
    foreach($options as $key=>$val) {
        if(is_string($val)) {
            $val = strtolower($val);
            if(!in_array($val, $req_types, true))
                throw new Exception($f1($val));
            $options2[] = $val;
        } elseif(is_int($val)) {
            if($val <= 0 || $val >= ($c1+1))
                throw new Exception($f1($val));
            $options2[] = $req_types[$val-1];
        } else {
            throw new Exception($f1($val));
        }
    }

    // generate routes
    foreach($options2 as $key=>$val) {
        if(!method_exists($class, $val))
            throw new Exception("Method `$val` not found in class `$class`");
        $n = $name.'.'.$val;
        $a = [$class, $val];
        match($val) {
            $req_types[0] =>
                Route::get($uri, $a)->name($n),                  // index
            $req_types[1] =>
                Route::get($uri.'/create', $a)->name($n),        // create
            $req_types[2] =>
                Route::post($uri, $a)->name($n),                 // store
            $req_types[3] =>
                Route::get($uri.'/{photo}', $a)->name($n),       // show
            $req_types[4] =>
                Route::get($uri.'/{photo}/edit', $a)->name($n),  // edit
            $req_types[5] =>
                Route::patch($uri.'/{photo}', $a)->name($n),     // update
            $req_types[6] =>
                Route::delete($uri.'/{photo}', $a)->name($n),    // destroy
            default => null,
        };
    }
}

function route_names(bool $withFilter = false)
{
    $routes = Route::getRoutes();
    $route_names = [];
    foreach($routes as $route) {
        $n = $route->getName();
        if(!is_string($n)) continue;
        $n = trim($n);
        $cond = !empty($n) && ($withFilter ? !Str::startsWith($n, ['generated::', 'debugbar', 'ignition']) : true);
        if($cond) $route_names[] = $n;
    }
    return $route_names;
}

/**
 * Generate root URIs
 *
 * @param string $uri
 * @return \Illuminate\Routing\Route|null
 */
function route_generate_root_uris()
{
    $func1 = function(string $uri) {
        $rdr_auth = auth()->check() ? '/home' : '/login';
        $srvr = request()->server() ?? [];
        $req_uri = rtrim($srvr['REQUEST_URI'] ?? '', '/');
        $host_url = rtrim(($srvr['REQUEST_SCHEME'] ?? '').'://'.($srvr['HTTP_HOST'] ?? ''), '/');
        $uri_esc = rtrim(Str::replace('/', '', $uri));
        $last = empty($uri_esc) ? $req_uri.$rdr_auth : Str::replaceLast($uri, $rdr_auth, $req_uri);
        $final = $host_url.$last;
        return redirect()->to($final);
    };
    foreach(config('root-uris', []) as $key=>$val) {
        Route::get($val, function() use($val, $func1) {
            return $func1($val);
        })->name('root'.($key+1).'.redirect');
    }
}

/**
 * Generate auth platform routes (OAuth)
 *
 * @param string $uri
 * @param string $platform
 * @param string $class
 * @return \Illuminate\Routing\Route|null
 */
function route_generate_auth_platform(string $platform)
{
    $methods = ['redirect', 'callback', 'deletion'];
    $platform = strtolower(preg_replace('/[^A-Za-z0-9]/u', '', $platform));
    $class = '\App\Http\Controllers\Guest\\'.ucwords($platform).'Controller';
    foreach($methods as $key=>$val) {
        //$config_key = strtoupper($platform.'_ROUTE_'.$val);
        $route_name = strtolower($platform.'.'.$val);
        //$config = config('env.'.$config_key);
        //if(empty($config))
        //    throw new exception('Config not found: '.$config_key);
        // Route::get($config, [$class, $val])->name($route_name);
        Route::get("/guest/$platform/$val", [$class, $val])->name($route_name);
    }
}

/**
 * Generates action url from resource index route
 *
 * @param string $route
 * @param string $action
 * @param int $id
 * @param bool $wrapAnchor
 * @param array $attr
 * @param mixed $value
 * @return string
 */
function route_url_action(string $route, string $action, int $id, bool $wrapAnchor = false, array $attr = [], $value = 'link')
{
    if(!Route::has($route))
        throw new exception('Non-existent route: '.$route);
    $v = '';
    switch($action) {
        case 'show':
            $v = route($route).'/'.$id.'/show';
            $attr = array_merge($attr, ['href' => $v]);
            break;
        default:
            throw new exception('Invalid action: `'.$action.'`');
            // break;
    }
    $pre = $wrapAnchor ? '<a '.(arr_implode_html_attr(' ', $attr)).'>' : '';
    $suf = $wrapAnchor ? '</a>' : '';
    $value = $wrapAnchor ? $value : $v;
    return $pre.$value.$suf;
}


function arr_implode_html_attr(string $glue, array $attributes)
{
    $dataAttributes = array_map(function($value, $key) {
        return $key.'="'.$value.'"';
    }, array_values($attributes), array_keys($attributes));
    $dataAttributes = implode($glue, $dataAttributes);
    return $dataAttributes;
}














/** -----------------------------------------------
 * SESSION
 */

/**
 * Gets alerts from the session
 *
 * @param boolean $delete_alerts_session delete session alerts after getting it
 * @return array
 */
function session_get_alerts(bool $delete_alerts_session = false)
{
    $key = config('z.base.session.alert.key');
    $alerts = (array)(session()->get($key) ?? []);
    if($delete_alerts_session)
        session()->forget($key);
    return $alerts;
}

/**
 * Gets errors from the session
 *
 * @return array
 */
function session_get_errors()
{
    $arr1 = [];
    try {
        $arr1 = (array)Session::all()['errors']->getBags()['default']->getMessages();
    } catch(\Exception $ex) {}
    return $arr1;
}

/**
 * Pushes values into the session
 *
 * @param string $key
 * @param mixed $val
 * @return void
 */
function session_push(string $key, $val)
{
    if(is_null(session()->get($key)))
        session()->put($key, []); // create key if not exists
    session()->push($key, $val);
}

/**
 * Gets a session value
 *
 * @param int|string|null $key
 * @return mixed
 */
function session_get($key)
{
    return session($key);
}

/**
 * Sets a session value
 *
 * @param string $key
 * @param mixed $val
 * @return void
 */
function session_set(string $key, $val)
{
    // if(is_null(session()->get($key)))
    //     session()->put($key, null); // create key if not exists
    session()->put($key, $val);
}

/**
 * Pushes an alert to the session
 *
 * `bs_class` => `[ primary, secondary, success, danger, warning, info, light, dark ]`
 *
 * @param string $status
 * @param string $msg
 * @param string $title
 * @param string $alert_type
 * @param boolean $safe_mode
 * @return void
 */
function session_push_alert($status, $msg, $title = '', $alert_type = 'toastr', bool $safe_mode = false) : void
{
    $alert_types = ['swal2', 'toastr'];

    try {
        if(!is_string($status)) throw new exception('$status must be string');
        if(!is_string($msg)) throw new exception('$msg must be string');
        if(!is_string($title)) throw new exception('$title must be string');
        if(!is_string($alert_type)) throw new exception('$alert_type must be string');
        // $alert_type = !empty($alert_type) ? $alert_type : $alert_type[1];
        if(!in_array($alert_type, $alert_types, true)) throw new exception('Invalid alert type');
    } catch(\Exception $ex) {
        if(webclient_is_dev()) {

        }
        if($safe_mode) goto point1;
        else throw new exception($ex->getMessage());
    }

    $val = ['status' => $status, 'msg' => $msg, 'type' => $alert_type, 'title' => $title];
    session_push(config('z.base.session.alert.key'), $val);
    point1:
}












/** -----------------------------------------------
 * SOCKET
 */

/**
 * IP & Port check
 *
 * @param string $ip
 * @param int|int[] $port single or multiple ports
 * @return <bool,string,array>
 *
 */
function socket_check(string $ip, $port = 80, float $timeout = 0.5)
{
    $is_success = false;
    if(!(is_int($port) || is_array($port)))
        throw new exception('$port must be int or int[]');
    $port = is_int($port) ? [$port] : $port;
    $port_range = [0, 65536];
    $ports = [];
    foreach($port as $k=>$v) {
        if($v >= $port_range[0] && $v <= $port_range[1]) {
            $ports[] = $v;
        }
    }
    $ports = array_unique($ports);
    $em = '';
    try {
        if(empty($ports))
            throw new exception('Port is empty');
        $error_report = [];
        $is_success = true;
        $firstError = '';
        foreach($ports as $k=>$v) {
            $error_code = null;
            $error_message = null;
            $fp = @fsockopen($ip, $v, $error_code, $error_message, $timeout);
            if(!$fp) {
                $error_report[$v] = [$error_code, $error_message];
                $firstError = !empty($firstError) ? $firstError : 'Connection failed';
            } else {
                $error_report[$v] = [];
            }
        }
        if(!empty($firstError)) {
            throw new exception($firstError);
        }
    } catch(\Exception $ex) {
        $em = $ex->getMessage();
        $is_success = false;
    }
    return [$is_success, $em, $error_report];
}










/** -----------------------------------------------
 * STORAGE
 */

function storage_file_info(string $path, $basename_new = null)
{
    /*
        $basename_new:
            === true ? same as $basename
            !empty() ? $basename_new
            else ? random 15 alphanum
    */

    $file = [
        'exists' => false,
        'path' => trim($path),
        'dir' => '',
        'name' => '',
        'basename' => '',
        'ext' => '',
        'mime_type' => null,
        'path_app' => '',  // relative path inside storage/app
        'dir_app' => '',
        'md5' => '',
    ];

    // $p = storage_path('app/'.$file['path']);
    $p = str_sanitize($file['path']);
    $p = trim($p, '/');
    $p = storage_path(Str::startsWith($p, 'app/') ? $p : 'app/'.$p);
    $file['path'] = $p;

    // CHECK IF PATH EXISTS
    $file['exists'] = (!empty($p) && File::exists($p) && is_file($p));
    if(!$file['exists'])
        goto point1;
    //if($file['exists'] !== true)
    //    throw new exception('File doesn\'t exists');

    // CHECK MIME TYPE
    $file['mime_type'] = false;
    try { $file['mime_type'] = File::mimeType($p); } catch(\Exception $ex) {}
    $file['mime_type'] = !is_string($file['mime_type']) ? '' : $file['mime_type'];
    //if(is_string($file['mime_type']) !== true || str_empty($file['mime_type']) === true)
    //    throw new exception('Invalid mime type');

    // FILE INFO PARTS
    //$file['dir'] = File::dirname($p);
    $file['name'] = basename($p);
    $file['dir'] = Str::of(Str::replaceLast($file['name'], '', $p))->rtrim('/')->__toString();
    $file['ext'] = Str::afterLast($file['name'], '.');
    $file['ext'] = ($file['name'] === $file['ext']) ? '' : $file['ext'];
    $file['basename'] = Str::replaceLast('.'.$file['ext'], '', $file['name']);
    $file['path_app'] = Str::replaceLast(storage_path('app/'), '', $p);
    $file['dir_app'] = Str::of(Str::replaceLast($file['name'], '', $file['path_app']))->rtrim('/')->__toString();
    $file['md5'] = $file['exists'] ? md5_file($p) : '';

    // NEW PATH
    $ext_ = str_empty($file['ext']) !== true ? '.'.$file['ext'] : '';
    if($basename_new === true)
        $file['path_new'] = $file['name'];
    else if(is_string($basename_new) && str_empty($basename_new) !== true)
        $file['path_new'] = $basename_new.$ext_;
    else
        $file['path_new'] = str_random_alphanum(15).$ext_;

    point1:
    return $file;
}

function storage_file_stream(Request $request) {
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
    $m = ((int)($request->get('m') ?? 0)) - 1;
    $p = (string)($request->get('p') ?? '');

    // CHECK STREAM MODE
    $mode = $modes[$m] ?? '';  // m -> stream mode
    if(in_array($mode, $modes, true) !== true)
        throw new Exception('Invalid stream mode');

    // CHECK FILE PATH INFO
    $crypt = crypt_sc($p, 1, true);
    if($crypt[0] !== true)
        throw new exception($crypt[1]);
    $path = $base_dir.$crypt[2];
    $path = Str::replaceFirst(storage_path('app/'), '', $path);

    // CHECK FILE PATH AND MIME TYPE
    $file = storage_file_info($path, null);
    if($file['exists'] !== true)
        throw new Exception('File doesn\'t exists');
    if(str_empty($file['mime_type']) === true)
        throw new Exception('Invalid mime type');

    // ROLE VALIDATION
    $hasAccess = StorageAccess::check($request, $file);
    if($hasAccess !== true)
        abort(403, 'Access denied');

    // FILE STREAM
    $headers = ['Content-Type: '.$file['mime_type'], 'Cache-Control: no-cache, no-store, must-revalidate, post-check=0, pre-check=0'];
    $dispositions = ['inline', 'attachment'];
    $disposition = $dispositions[$m];
    $filestream = Response::download($file['path'], $file['path_new'], $headers, $disposition);

    return $filestream;
}

function storage_file_url(string $path, string $mode) {
    // explode segments

    // SETTINGS
    $url_len_max = 2000;
    $modes = ['dispose', 'download'];

    // check mode
    if(!in_array($mode, $modes))
        throw new exception('Invalid mode');
    $m = array_search($mode, $modes) + 1;

    // check if path exists
    $file = storage_file_info($path, true);

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









/** -----------------------------------------------
 * STRING
 */


/**
 * Converts a string into a PHP constant variable
 *
 * @param string $strvar
 * @return void
 */
 function str_constant_variable(string $input, $isUppercase = true)
 {
     $formatted = $isUppercase ? strtoupper($input) : strtolower($input);
     $sanitized = str_replace([' ', '-'], '_', $formatted);
     $pattern = $isUppercase ? '/[^A-Z0-9_]/' : '/[^a-z0-9_]/';
     $constantName = preg_replace($pattern, '', $sanitized);
     return $constantName;
 }

/**
 * Compares two "PHP-standardized" version number strings
 *
 * @param string $version1
 * @param string $version2
 * @param string|null $operator
 * @return int|bool
 */
function str_version_compare(string $version1, string $version2, ?string $operator)
{
    return version_compare($version1, $version2, $operator);
}

/**
 * Check if version1 is >= to version2
 *
 * @param string $version1
 * @param string $version2
 * @return bool
 */
function str_version_ge(string $version1, string $version2)
{
    return (bool)str_version_compare($version1, $version2, '>=');
}

/**
 * Matches string with a pattern
 *
 * @param string $pattern
 * @param string $subject
 * @return bool
 */
function str_preg_match(string $pattern, string $subject)
{
    return preg_match($pattern, $subject) === 1;
}

/**
 * Trim unicode/UTF-8 whitespace in PHP
 * - Replaces any weird whitespace characters or control characters INTO space (ascii 32)
 * - Replaces chained spaces into one space
 * - Trims leading and trailing spaces
 *
 * @param string $str
 * @param boolean $one_space
 * @param boolean $with_trim
 * @return string
 */
function str_sanitize(string $str, bool $one_space = true, bool $with_trim = true)
{
    $charcode_preserve = [9, 32];  // tab, space
    $str_split = mb_str_split($str);
    $new_str1 = '';
    foreach($str_split as $key=>$val) {
        $ch_ord = ord($val);
        if(in_array($ch_ord, $charcode_preserve)) {
            $new_str1 .= $val;
        } else if(str_preg_match('/^[\pZ\pC]+|[\pZ\pC]+$/u', $val)) {
            $new_str1 .= ' ';
        } else {
            $new_str1 .= $val;
        }
    }
    $new_str2 = $one_space ? (string)(preg_replace('/\s+/u', ' ', $new_str1)) : $new_str1;
    $new_str3 = $with_trim ? trim($new_str2) : $new_str2;
    return $new_str3;
}

/**
 * Test if string is empty
 * - Detects weird whitespace characters (e.g. code 255 & etc)
 *
 * @param string $str
 * @return bool
 */
function str_empty(string $str)
{
    return (bool)empty(str_sanitize($str));
}

/**
 * Test if string is filled
 * - Detects weird whitespace characters (e.g. code 255 & etc)
 *
 * @param string $str
 * @return bool
 */
function str_filled(string $str)
{
    return !str_empty($str);
}

/**
 * Returns $val_true if $str is not empty, otherwise $val_false
 *
 * @param string $str
 * @param mixed $val_true
 * @param mixed $val_false
 * @return mixed
 */
function str_filled_eval(string $str, $val_true, $val_false)
{
    return str_filled($str) ? $val_true : $val_false;
}

/**
 * Returns $str if it's not empty, otherwise $val_false.
 *
 * @param string $str
 * @param mixed $val_false
 * @return mixed
 */
function str_filled_eval_self(string $str, $val_false)
{
    return str_filled_eval($str, $str, $val_false);
}

/**
 * Removes non alpha-numeric or underscore characters
 *
 * @param string $str
 * @return string
 */
function str_only_alphanumus(string $str)
{
    return preg_replace("/[^A-Za-z0-9_]/", '', $str);
}

function str_excerpt(string $str, $phrase = '', $options = [])
{
    return Str::of($str)->excerpt($phrase, $options);
}

function str_limit(string $str, $limit = 100, $end = '...')
{
    return Str::of($str)->limit($limit, $end);
}

function str_random_alphanum(int $min_length = 10, int $max_length = 0)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    $max_length = $max_length <= 0 ? $min_length : $max_length;
    $length = rand($min_length, $max_length);

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Evaluate pattern to subject and if true, manipulate subject value in Closure.
 *
 * @param string $pattern Regular expression
 * @param string $subject
 * @param \Closure $true_func Params (string $pattern, string $subject, mixed $output)
 * @return null|mixed
 */
function str_regex_eval(string $pattern, string $subject, $true_func = null)
{
    $output = null;
    if(str_preg_match($pattern, $subject)) {
        if(var_is_closure($true_func) !== true) {
            $output = !is_null($true_func) ? $true_func : $subject;
        } else {
            $output = $true_func->__invoke($pattern, $subject, null);
        }
    }
    return $output;
}

/**
 * Gets the standard QWERTY keyboard symbols
 *
 * @param boolean $escape
 * @return string
 */
function str_keyboard_symbols(bool $escape = false)
{
    $output = '';
    $symbols = "!@#$%^&*()-_=+[{]};:'".'"'."\|,<.>/?`~";  // double quotes intentionally isolated
    if(!$escape) goto point1;
    $pcs = str_split($symbols);
    foreach($pcs as $key=>$val) {
        $output .= $pcs[22].$val;
    }
    point1:
    return $output;
}

/**
 * Replace the last occurrence of a given value in the string.
 *
 * @param string $search
 * @param string $replace
 * @param string $subject
 * @return string
 */
function str_replace_last($search, $replace, $subject)
{
    return Str::replaceLast($search, $replace, $subject);
}





























/** -----------------------------------------------
 * URL
 */

/**
 * Quick parse of url string
 *
 * @param string $url
 * @param integer $component
 * @return array
 * @before `url_parse()`
 */
function url_parse_short(string $url, int $component = -1)
{
    $types = [
        'scheme' => 'string',
        'host' => 'string',
        'port' => 'int',
        'user' => 'string',
        'pass' => 'string',
        'path' => 'string',
        'query' => 'string',
        'fragment' => 'string',
    ];
    $a = parse_url($url, $component);
    $fn_arr_get = function(string $key) use($a, $types) {
        $val = $val2 = ($a[$key] ?? null);
        if(!empty($val2) && array_key_exists($key, $types)) {
            settype($val2, $types[$key]);
        }
        return $val2;
    };
    $ret = ['is_valid' => ($a !== false)];
    $ret['urlRaw'] = $ret['is_valid'] ? $url : '';
    $ret['urlRawDecode'] = $ret['is_valid'] ? urldecode($url) : '';
    foreach($types as $k=>$v) {
        $ret[$k] = $fn_arr_get($k);
    }
    $ret['username'] = $ret['user'];
    $ret['password'] = $ret['pass'];
    $ret['credentialRaw'] = $ret['user'].(!empty($ret['pass']) ? ':'.$ret['pass'] : '');
    $ret['queryRaw'] = (string)($ret['query'] ?? '');

    $ret['queryRawDecode'] = urldecode($ret['queryRaw']);

    parse_str($ret['queryRaw'], $ret['query']);
    $ret['urlParsed'] = $ret['is_valid'] ? $ret['scheme'].'://'
        .(!empty($ret['credentialRaw']) ? $ret['credentialRaw'].'@' : '')
        .$ret['host'].(!empty($ret['port']) ? ':'.$ret['port'] : '').$ret['path']
        .(!empty($ret['queryRaw']) ? '?'.$ret['queryRaw'] : '')
        .(!empty($ret['fragment']) ? '#'.$ret['fragment'] : '')
    : '';
    $ret['is_same_url'] = $ret['urlRaw'] === $ret['urlParsed'];
    return $ret;
}

/**
 * Comprehensive parsing of url string
 *      *
 * @param string $url
 * @param string $defaultScheme
 * @return object
 *
 * @requires `\Illuminate\Support\Str`
 * @depends `url_parse_short()`
 */
function url_parse_(string $url, string $defaultScheme = 'https')
{
    // if(!Str::startsWith($url, ['http://', 'https://']))
    // 	throw new exception('$url must starts with `http` or `https`');

    $url = trim($url);
    $urlOld = $url;
    $is_scheme_adjusted = false;
    $allowed_schemes = ['http://', 'https://', 'ftp://', 'ftps://', 'ftpes://'];

    $replaceFirst = ['//', '://'];
    foreach($replaceFirst as $k=>$v) {
        if(Str::startsWith($url, $v)) {
            $url = Str::replaceFirst($v, '', $url);
            break;  // check only once
        }
    }

    if(Str::startsWith($url, ['//', '://'])) {
        $url = Str::replaceFirst('://', '', $url);
    }

    if(!Str::startsWith($url, $allowed_schemes)) {
        $url = $defaultScheme.'://'.$url;
        $is_scheme_adjusted = true;
    }

    $parsed = url_parse_short($url);
    if(!$parsed['is_valid']) {
        goto point1;
    }

    $has_username = !empty($parsed['username']);
    $has_password = !empty($parsed['password']);
    $has_port = !empty($parsed['port']);
    $has_credential = $has_username && $has_password;
    $has_query = !empty($parsed['query']);
    $has_fragment = !empty($parsed['fragment']);

    try {
        $is_path_root = empty(trim(trim($parsed['path'], '/')));
    } catch(\Throwable $ex) {

    }


    $schemeHostPath = $parsed['scheme'].'://'.$parsed['host'].$parsed['path'];
    $pathQueryFragment = $parsed['path'].($has_query ? '?'.$parsed['queryRaw'] : '').($has_fragment ? '#'.$parsed['fragment'] : '');
    $path2 = ($parsed['path'][0] ?? '') === '/' ? substr($parsed['path'], 1) : $parsed['path'];
    $pathArr = $is_path_root ? [] : (array)explode('/', $path2);
    $fragment2 = (string)(($parsed['fragment'][0] ?? '') === '/' ? substr($parsed['fragment'], 1) : $parsed['fragment']);
    $fragmentArr = (array)explode('/', $fragment2);

    point1:
    $ret = [
        'is_valid' => $parsed['is_valid'],
        'scheme' => $parsed['scheme'],
        'username' => $parsed['username'],
        'password' => $parsed['password'],
        'host' => $parsed['host'],
        'port' => $parsed['port'],
        'path' => $parsed['path'],
        'query' => $parsed['query'],
        'fragment' => $parsed['fragment'],  // string only, not exploded

        'credentialRaw' => $parsed['credentialRaw'],
        'queryRaw' => $parsed['queryRaw'],
        'queryRawDecode' => $parsed['queryRawDecode'],
        'queryDepth' => arr_depth($parsed['query']),

        'urlRaw' => $parsed['urlRaw'],
        'urlRawDecode' => $parsed['urlRawDecode'],
        'urlParsed' => $parsed['urlParsed'],

        'segmentsPath' => $pathArr,
        'segmentsFragment' => $fragmentArr,

        'is_scheme_adjusted' => $is_scheme_adjusted,
        'is_path_root' => $is_path_root,
        'has_port' => $has_port,
        'has_username' => $has_username,
        'has_password' => $has_password,
        'has_credential' => $has_credential,
        'has_query' => $has_query,
        'has_fragment' => $has_fragment,

        'countSegmentFragment' => count($fragmentArr),
        'countSegmentPath' => count($pathArr),

        'schemeHostPath' => $schemeHostPath,
        'pathQueryFragment' => $pathQueryFragment,

        // 'obj' => null,
    ];

    return (object)$ret;
}

// OLD
function url_parse(string $url, string $defaultScheme = 'https')
{
    // if(!Str::startsWith($url, ['http://', 'https://']))
    // 	throw new exception('$url must starts with `http` or `https`');

    $url = trim($url);
    $urlOld = $url;
    $is_scheme_adjusted = false;
    $allowed_schemes = ['http://', 'https://', 'ftp://', 'ftps://', 'ftpes://'];

    $replaceFirst = ['//', '://'];
    foreach($replaceFirst as $k=>$v) {
        if(Str::startsWith($url, $v)) {
            $url = Str::replaceFirst($v, '', $url);
            break;  // check only once
        }
    }

    if(Str::startsWith($url, ['//', '://'])) {
        $url = Str::replaceFirst('://', '', $url);
    }

    if(!Str::startsWith($url, $allowed_schemes)) {
        $url = $defaultScheme.'://'.$url;
        $is_scheme_adjusted = true;
    }

    $fn0 = function() use($url) {
        return [
            'is_valid' => false,
            // 'fullUrl' => '',
            'url' => '',
            'scheme' => '',
            'username' => null,
            'password' => null,
            'host' => '',
            'port' => null,
            'path' => '',
            'query' => [],
            'fragment' => '',  // string only, not exploded

            'credentialRaw' => '',
            'queryRaw' => '',
            'urlRaw' => $url,
            'pathArr' => [],
            'fragmentArr' => [],
            'schemeHostPath' => '',
            'pathQueryFragment' => '',

            'is_scheme_adjusted' => false,
            'is_path_root' => false,
            'has_port' => false,
            'has_username' => false,
            'has_password' => false,
            'has_credential' => false,
            'has_query' => false,
            'has_fragment' => false,

            'obj' => null,
        ];
    };

    $queryStr = function(array $query) {
        $s = '';
        $x = -1;
        foreach($query as $k=>$v) {
            $x++;
            $s .= (($x > 0) ? '&' : '').$k.'='.($v ?? null);
        }
        return $s;
    };

    try {
        if(in_array($url, $allowed_schemes, true)) {
            throw new exception('Only scheme was present');
        }

        $r = $fn0();
        $d = SpatieUrl::fromString($url);

        $u = explode(':', $d->getUserInfo());

        $fn1 = function(bool $isHttps) { return $isHttps ? 'https' : 'http'; };
        // $isUrlHttps = Str::startsWith($url, 'https://');
        // $isAppUrlHttps = Str::startsWith(config('app.url'), 'https://');
        // $shouldAdjust = ($adjustScheme && ($isUrlHttps !== $isAppUrlHttps));
        $username = (string)($u[0] ?? '');

        // $r['scheme'] = (string)($shouldAdjust ? $fn1($isAppUrlHttps) : $fn1($isUrlHttps));
        $r['scheme'] = !empty($d->getScheme()) ? $d->getScheme() : $defaultScheme;
        $r['host'] = $d->getHost();
        $r['port'] = $d->getPort();
        $r['username'] = !empty($username) ? $username : null;
        $r['password'] = ($u[1] ?? null);
        $r['path'] = $d->getPath();
        $r['query'] = $d->getAllQueryParameters();
        $r['fragment'] = $d->getFragment();
        $r['queryRaw'] = $queryStr($r['query']);
        $r['is_scheme_adjusted'] = $is_scheme_adjusted;
        $r['is_path_root'] = empty(trim(trim($r['path'], '/')));
        $r['schemeHost'] = $r['scheme'].'://'.$r['host'];
        $r['schemeHostPath'] = $r['scheme'].'://'.$r['host'].$r['path'];

        $r['has_port'] = !empty($r['port']);
        $r['has_query'] = !empty($r['query']);
        $r['has_fragment'] = !empty($r['fragment']);
        $r['has_username'] = !empty($r['username']);
        $r['has_password'] = !empty($r['password']);
        $r['has_credential'] = $r['has_username'] && $r['has_password'];

        $r['pathQueryFragment'] = $r['path'].($r['has_query'] ? '?'.$r['queryRaw'] : '').($r['has_fragment'] ? '#'.$r['fragment'] : '');

        $r['credentialRaw'] = $r['has_credential'] ? $r['username'].':'.$r['password'] : '';
        $path2 = ($r['path'][0] ?? '') === '/' ? substr($r['path'], 1) : $r['path'];
        $fragment2 = ($r['fragment'][0] ?? '') === '/' ? substr($r['fragment'], 1) : $r['fragment'];
        $r['pathArr'] = $r['is_path_root'] ? [] : (array)explode('/', $path2);
        $r['fragmentArr'] = (array)explode('/', $fragment2);
        $r['url'] = $r['scheme'].'://'
            .($r['has_credential'] ? $r['credentialRaw'].'@' : '')
            .$r['host'].($r['has_port'] ? ':'.$r['port'] : '').$r['path']
            .($r['has_query'] ? '?'.$r['queryRaw'] : '')
            .($r['has_fragment'] ? '#'.$r['fragment'] : '')
        ;

        $r['obj'] = $d;
        $r['is_valid'] = true;

    // } catch(\Exception $ex) {
    } catch(\Throwable $ex) {
        $r = $fn0();
    }

    point1:
    return (object)$r;
}












/* -----------------------------------------------
 * VALIDATE
 */

/**
 * Simple validation
 *
 * @param array $data `[ key => val ]`
 * @param array $rules `[ key => rules[] ]`
 * @return bool
 * @requires `\Illuminate\Support\Facades\Validator`
 */
function validate_simple(array $data, array $rules)
{
    return !Validator::make($data, $rules)->fails();  // LARAVEL DEPENDENT
}

/**
 * Validates a URL string
 *
 * @param string $url
 * @return bool
 */
function validate_url(string $url)
{
    return validate_simple(['url' => $url], ['url' => ['required', 'url']]);
}

/**
 * Validates an IP string (v4 or v6)
 *
 * @param string $ipv46
 * @return bool
 */
function validate_ipv46(string $ipv46)
{
    return validate_simple(['ip' => $ipv46], ['ip' => 'required|ip']);
}

/**
 * Validates an IPv4 string
 *
 * @param string $ipv4
 * @return bool
 */
function validate_ipv4(string $ipv4)
{
    return validate_simple(['ipv4' => $ipv4], ['ipv4' => 'required|ipv4']);
}

/**
 * Validates an IPv6 string
 *
 * @param string $ipv6
 * @return bool
 */
function validate_ipv6(string $ipv6)
{
    return validate_simple(['ipv6' => $ipv6], ['ipv6' => 'required|ipv6']);
}

/**
 * Validates email string
 *
 * @param string $email
 * @return bool
 */
function validate_email(string $email)
{
    return validate_simple(['email' => $email], ['email' => 'required|regex:'.'/^([A-Za-z0-9_]+){1}([\.]?[A-Za-z0-9_]+)*([@]){1}(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/u']);
}

/**
 * Validates date, time, and microsecond
 *
 * @param string $dts e.g. `'2022-09-20 01:05:50.842924'`
 * @return bool
 */
function validate_datetime(string $dts)
{
    // return validate_simple(['dts' => $dts], ['dts' => 'required|regex:'.'/^([0-9]{4})\-(0[1-9]|1[0-2])\-(0[1-9]|[12][0-9]|3[01])\ (0[0-9]|1[0-9]|2[0-3])\:([0-5][0-9])\:([0-5][0-9])\.([0-9]{6})$/u']);
    return str_preg_match('/^([0-9]{4})\-(0[1-9]|1[0-2])\-(0[1-9]|[12][0-9]|3[01])\ (0[0-9]|1[0-9]|2[0-3])\:([0-5][0-9])\:([0-5][0-9])\.([0-9]{6})$/u', $dts);
}


















/** -----------------------------------------------
 * VAR
 */

/**
 * Casts variable to the specified type
 *
 * @param mixed $var
 * @param string $type
 * @return mixed
 */
function var_cast($var, string $type)
{
    /*return match($type) {
        'NULL'      => null,
        'string'    => (string) $var,
        'integer'   => (integer) $var,
        'float'     => (float) $var,
        'double'    => (double) $var,
        //'real'      => (real) $var,  // removed
        'array'     => (array) $var,
        'boolean'   => (boolean) $var,
        'object'    => (object) $var,
        default     => $var,
    };*/
        $type2 = var_type_official($type);
        settype($var, strtolower($type2));
    return $var;
}

/**
 * Checks if var is a closure
 *
 * @param Closure|string $obj
 * @return bool
 */
function var_is_closure($obj) {
    $bool = false;
    try {
        $reflection = new \ReflectionFunction($obj);
        $bool = (bool)$reflection->isClosure();
    } catch(\Throwable $th) {}
    return $bool;
}

/**
 * Stringify the value
 *
 * @param mixed $var
 * @return string
 */
function var_stringify($var) {
    if($var === true) return 'true';
    elseif($var === false) return 'true';
    elseif($var === null) return 'null';
    elseif(is_array($var)) return json_encode($var);
    elseif(is_string($var)) return '"'.$var.'"';
    return (string)$var;
}

/**
 * Gets the PHP data types
 *
 * - as of `PHP 8.1.2`
 *
 * @return <int,string>
 */
function var_types() {
    return [
        'NULL',
        'string',
        'integer',
        'float',
        'double',
        //'real',  // removed
        'boolean',
        'array',
        'object',
        'resource',
    ];
}

/**
 * Gets the official PHP data type
 *
 * - as of `PHP 8.1.2`
 *
 * @param string $type
 * @return string
 * @throws \Exception
 */
function var_type_official(string $type)
{
    $type = strtolower($type);
    $o = [  // overrides
        'null'  => 'NULL',
        'str'   => 'string',
        'int'   => 'integer',
        'bool'  => 'boolean',
        'arr'   => 'array',
        'obj'   => 'object',
        'res'   => 'resource',
        'real'  => 'float',  // real converted to float
    ];

    $type = array_key_exists($type, $o) ? $o[$type] : $type;
    $types = var_types();
    if(!in_array($type, $types, true))
        throw new exception('Invalid type: '.$type);
    $t = []; foreach($types as $k=>$v) { $t[$v] = $v; }
    // $type2 = array_key_exists($type, $o) ? $type[$o] : $type;
    $type2 = array_key_exists($type, $o) ? $o[$type] : $type;
    if(!array_key_exists($type2, $t))
        throw new exception('Invalid data type: '.$type2);

    return $t[$type2];
}
















/** -----------------------------------------------
 * VIEW
 */

/**
 * Gets the title for the page
 *
 * @param string $title
 * @param boolean $include_app_name
 * @param string $separator
 * @return string
 */
function view_title(string $title, bool $include_app_name=true, string $separator = ' | ')
{
    $app_name = trim($include_app_name ? (string)env('APP_NAME') : '');
    $ea = empty($app_name);  // empty app name
    $title = trim($title);
    $et = empty($title);  // empty title
    $final = $title.(($include_app_name && !$ea) ? (!$et ? $separator : '').$app_name : '');
    return $final;
}

/**
 * Gets the view variable
 *
 * @param string $key
 * @param boolean $strict
 * @return mixed
 */
function view_variable(string $key, bool $strict = false)
{
    $v = [];
    if(isset($__data)) {
        $v = $__data;
    } else {
        if($strict) throw new exception('$__data is not declared');
    }
    if(Arr::exists($v, $key)) {
        return Arr::get($v, $key);
    } else {
        if($strict) throw new exception('Key `'.$key.'` is not declared');
        return null;
    }
}














/** -----------------------------------------------
 * WEBCLIENT
 */

/**
 * Gets the intended URL of webclient
 *
 * @return string default `'/'`
 */
function webclient_intended()
{
    $curr_url = url()->current();
    $prev_url = url()->previous();

    $parsed_prev_url = url_parse($prev_url);
    $prev_url_path = $parsed_prev_url->url ?? '';

    $except = [  // guest pages
        // route('index.index'),               // /
        route(env('ROUTE_LOGIN')),                     // /login
        route(env('ROUTE_REGISTER')),                  // /register
        // route('auth.fb.redirect'),          // /auth/facebook/redirect
        // route('auth.fb.callback'),          // /auth/facebook/callback
        // route('auth.fb.deletion'),          // /auth/facebook/deletion
    ];
    //$URIs_ignored = in_array($curr_url, $except) ? $except : [];
    $URIs_ignored = $except;
    $bool1 = (!empty($prev_url_path) && !in_array($prev_url_path, $URIs_ignored) && $curr_url !== $prev_url);
    return $bool1 ? $prev_url : '/';
}

/**
 * Checks if webclient is in developer mode
 *
 * @return bool
 */
function webclient_is_dev()
{
    // return !empty(config('z.base.key')) && ((string)(request()->cookie('dev') ?? '')) === config('z.base.key');
    return !empty(config('z.base.key'))
        && !empty(config('z.base.val'))
        && request()->hasCookie(config('z.base.key'))
        && ((string)(request()->cookie('dev') ?? '')) === config('z.base.val')
    ;
}

/**
 * Gets the webclient timezone
 *
 * @return string
 */
function webclient_timezone()
{
    return (string)config('user.settings.timezone', 'Asia/Taipei');
}















/** -----------------------------------------------
 * WEBSITE
 */

/**
 * Checks a website if it returns code `200` or `302`
 *
 * @param string $url
 * @return bool
 */
function website_check(string $url, bool $ignore_ssl = false, array $options = [])
{
	// $ignore_ssl not working

	$defaultOptions = [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_TIMEOUT => 15,
	];
	$defaultOptions = $ignore_ssl ? [CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0, ] : $defaultOptions;
	$finalOptions = [];
	foreach($defaultOptions as $k=>$v) { $finalOptions[$k] = $v; }
	foreach($options as $k=>$v) { $finalOptions[$k] = $v; }
	try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);

		foreach($finalOptions as $k=>$v) {
			curl_setopt($ch, $k, $v);
		}

		$http_respond = trim(strip_tags(curl_exec($ch)));
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(!in_array($http_code, ["200", "302"])) {
			throw new exception('Invalid http code');
		}
	} catch(\Throwable $ex) {
		return false;
	}
	return true;
}







