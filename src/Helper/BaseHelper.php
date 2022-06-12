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

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Spatie\Url\Url as SpatieUrl;
use Rguj\Laracore\Request\Request;
use Rguj\Laracore\Middleware\ClientInstanceMiddleware;



/* -----------------------------------------------
 * DEFINERS
 */

define('CONFIG_ENV_KEY', 'env');
define('CONFIG_UNV_KEY', 'unv');


/* -----------------------------------------------
 * REQUIRE
 */

if(file_exists(__DIR__.'/CurrentUserHelper.php'))
    require_once __DIR__.'/CurrentUserHelper.php';

if(file_exists(__DIR__.'/ThemeUtilHelper.php'))
    require_once __DIR__.'/ThemeUtilHelper.php';










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
 * @param  string|null  $key
 * @param  mixed  $value
 * @return array
 */
function arr_set(&$array, $key, $value)
{
    return Arr::set($array, $key, $value);
}

/**
 * Get the number of array depth
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
 * Is array structure sequential
 * Supports only one dimension
 *
 * @param array $arr
 * @return bool
 */
function arr_type_seq(array $arr) {
    return arr_type($arr) === 'sequential';
}

/**
 * Is array structure associative
 * - Supports only one dimension
 *
 * @param array $arr
 * @return bool
 */
function arr_type_assoc(array $arr) {
    return arr_type($arr) === 'associative';
}

/**
 * Parses array and returns the structure 
 * - "empty", "sequential", "associative", "mixed", "irregular"
 * - Supports up to 2 dimension only
 * - Auto converts null => '', false => 0, true => 1, decimals => integer
 * - Associative can be "int" or "string" that doesn't match the counter sequentially
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

function arr_colval_exists($needle, array $haystack, string $col_key, bool $strict=false)
{
    return in_array($needle, array_column($haystack, $col_key), $strict);
}











/** -----------------------------------------------
 * BLADE
 */

function blade_get_with()
{
    return View::getShared();
}

function blade_route($name, $parameters = [], $absolute = true)
{
    return route($name, $parameters, $absolute);
}

function blade_error(string $key)  // render attr errors
{
    if(!session()->has('errors')) return '';
    $errors = session('errors')->get($key) ?? [];
    if(empty($errors)) return '';
    $str = '<div class="text-danger">';
    foreach($errors as $error) {
        $str .= '<div class="mt-2">'.$error.'</div>';
    }
    $str .= '</div>';
    return $str;
}

function blade_purpose(string $purpose, int $index = 0)
{
    $vars = blade_get_with();
    return json_encode(
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
function class_controller_method(string $class, string $method, array $args = [], bool $strict = false)
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
    $parents = class_parents($type);
    $requiredParent = 'App\Http\Requests\Request';
    if(!array_key_exists($requiredParent, $parents)) {
        if($strict) throw new exception('Required class parent (first argument): '.$requiredParent);
        goto point1;
    }

    // remove first arg if its Request
    $firstArg = $args[0] ?? null;
    if(is_object($firstArg) && array_key_exists('Symfony\Component\HttpFoundation\Request', class_parents($firstArg))) {
        array_shift($args);
    }

    // insert request object
    $req = resolve($type);
    array_unshift($args, $req);

    point1:
    $ret = (new $class)->$method(...$args);
    return $ret;
}













/** -----------------------------------------------
 * CONDITION
 */

function cond_return(bool $cond, $true, $false)
{
    return $cond ? $true : $false;
}
















/** -----------------------------------------------
 * CONFIG
 */

/**
 * Gets the official PHP data type
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
    $types = [
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
    $t = []; foreach($types as $k=>$v) { $t[$v] = $v; }
    $type2 = array_key_exists($type, $o) ? $type[$o] : $type;
    // if(!in_array($type2, $types, true))
    if(!array_key_exists($type2, $t))
        throw new exception('Invalid data type: '.$type2);
    return $t[$type2];
}

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
 */
function config_unv_set(string $key, $val)
{
    config()->set(CONFIG_UNV_KEY.'.'.$key, $val);
}

/**
 * Get environment config
 *
 * @param string $key
 * @param mixed $val
 * @return mixed
 */
function config_env($key = null, $default = null) {
	return config(CONFIG_ENV_KEY.(!is_null($key) ? '.'.$key : ''), $default);	
}


/**
 * Get universal config
 *
 * @param string $key
 * @param mixed $val
 * @return mixed
 */
function config_unv($key = null, $default = null) {
	return config(CONFIG_UNV_KEY.(!is_null($key) ? '.'.$key : ''), $default);	
}















/** -----------------------------------------------
 * SECURITY
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
function crypt_de_merge_get(Request &$request, string $key, bool $is_post, bool $strict=true)
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
 * do not forget to typehint the `$q` in the closure
 * 
 * 
 * @usage `/** @var \Illuminate\Database\Query\Builder $q *\/ list($t, $p) = db_relation_info($q);`
 *
 * @param \Illuminate\Database\Eloquent\Relations\Relation $query
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
    try {        
        // check empty string
        if(str_empty($connection) === true)
            throw new exception('Empty $connection');

        // check connection
        try {
            /** @var \Illuminate\Database\Connection $c */
            $c = DB::connection($connection);
            $c->getPdo();
        } catch(\Exception $ex2) {
            throw new exception('Invalid connection: '.$connection.'');
        }

        // check table
        if(str_filled($table)) {
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
    DB::beginTransaction();
    try {
        $obj = DB::connection($conn)->table($table);
        $where_db = $obj;
        foreach($where as $k=>$v) {
            if($case_sensitive)
                $where_db->whereRaw('BINARY `'.$k.'` = ? ', [$v], 'and');
            else
                $where_db->where($k, '=', $v, 'and');
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

        DB::commit();
    } catch(\Exception $ex) {
        DB::rollBack();
        dd($ex);
    }

    if($id <= 0)
        throw new exception('Value of `id` must be UNSIGNED');

    return $id;
}


function db_stored_procedure(string $conn, string $func_name, array $binding=[], bool $to_array=false)
{
	// $FR_app = FieldRules::getGeneral();
	$kw = 'CALL ';  // keyword starts with, with 1 space
	$func_name = str_sanitize($func_name);
	// if(AppFn::STR_preg_match($FR_app['function']['regex'], $func_name) !== true)
	//     throw new exception('`$func_name` must be a valid function name');
	$param = '';
	$x = -1;
	foreach($binding as $key=>$val) {
		$param .= ((++$x)>0 ? ',' : '').'?';
	}
	$sql = $kw.$func_name.'('.$param.')';//dd($param);
	$select = DB::connection($conn)->select($sql, $binding);
	$output = $to_array ? obj_reflect($select) : $select;
	return $output;
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
    // dump($var);
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
    // dump($var);
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
        $ret = $v['at']['return'] ?? '';//dd($ret);
        $ret = (!empty($ret) && $ret !== 'void') ? docu_type_sanitize($ret) : 'void';
        // dd($ret);
        // dump($ret);
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
        // dd($ret);
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
 * @param Carbon\Carbon $obj
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
 * @property bool is_valid
 * @param string $dt_str
 * @param array|string $dt_format [ from, to ] | from
 * @param array|string $tz [ from, to ] | from
 * @return \App\Traits\DT
 * @uses Carbon\Carbon
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
    $dt_ = false;
    try { $dt_ = Carbon::createFromFormat($format_fm, $dt_str, $tz_fm); } catch (\Exception $ex) {}
    if(!dt_is_carbon($dt_)) goto point1;
    $dt2 = $dt_->clone()->setTimeZone($tz_to);
    if(!dt_is_carbon($dt2)) goto point1;
    $str_fm = $dt_->format($format_fm);
    $str_to = $dt2->format($format_to);        
    $output[0] = true;
    $output[1] = [$format_fm, $format_to];
    $output[2] = [$tz_fm, $tz_to];
    $output[3] = [$dt, $dt2];
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
    return dt_parse($dt_str, $dt_format, $tz)->string->onto;
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
function obj_reflect($obj, bool $to_array=false, $default=null)
{
    $reflector = function($obj1, bool $to_array=false) {
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
 * ROUTE
 */


function route_parse_url(string $url, bool $adjustScheme = true)
{
	if(!Str::startsWith($url, ['http://', 'https://']))
		throw new exception('$url must starts with `http` or `https`');

	$d = SpatieUrl::fromString($url);
	$u = explode(':', $d->getUserInfo());

	$fn1 = function(bool $isHttps) { return $isHttps ? 'https' : 'http'; };
	$isUrlHttps = Str::startsWith($url, 'https');
	$isAppUrlHttps = Str::startsWith(config_env('APP_URL'), 'https');
	$shouldAdjust = ($adjustScheme && ($isUrlHttps !== $isAppUrlHttps));
	$scheme = $shouldAdjust ? $fn1($isAppUrlHttps) : $fn1($isUrlHttps);
	$fullUrl = $scheme.strstr($url, ':');

	$r = [
		'scheme' => $scheme, //$d->getScheme(),
		'host' => $d->getHost(),
		'port' => $d->getPort(),
		'user' => $u[0] ?? '',
		'password' => $u[1] ?? null,
		'path' => $d->getPath(),
		'query' => $d->getAllQueryParameters(),
		'fragment' => $d->getFragment(),
		'is_scheme_adjusted' => $shouldAdjust,
		'obj' => $d,
	];
	$r['scheme'] = $scheme;
	$r['url'] = $scheme.'://'.$r['host'].(!empty($r['port']) ? ':'.$r['port'] : '').$r['path'];
	$r['fullUrl'] = $fullUrl;
	return (object)$r;
}


/**
 * Generates route that returns view
 *
 * @param string $uri
 * @param string $view
 * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
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
 * @return \Illuminate\Routing\Route
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
 * @return \Illuminate\Routing\Route
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
 * @return \Illuminate\Routing\Route
 */
function route_generate_auth_platform(string $platform)
{
    $methods = ['redirect', 'callback', 'deletion'];
    $platform = strtolower(preg_replace('/[^A-Za-z0-9]/u', '', $platform));
    $class = '\App\Http\Controllers\Guest\\'.ucwords($platform).'Controller';
    foreach($methods as $key=>$val) {
        //$config_key = strtoupper($platform.'_AUTH_ROUTE_'.$val);
        $route_name = strtolower($platform.'.'.$val);
        //$config = config('env.'.$config_key);
        //if(empty($config))
        //    throw new exception('Config not found: '.$config_key);
        // Route::get($config, [$class, $val])->name($route_name);
        Route::get("/guest/$platform/$val", [$class, $val])->name($route_name);
    }
}














/** -----------------------------------------------
 * SESSION
 */

function session_get_alerts(bool $delete_alerts_session = false)
{
    $key = (string)config('env.APP_SESSION_ALERTS_KEY');
    $alerts = session()->get($key) ?? [];
    if($delete_alerts_session)
        session()->forget($key);
    return $alerts;
}

function session_get_errors()
{
    $arr1 = [];
    try {
        $arr1 = Session::all()['errors']->getBags()['default']->getMessages();
    } catch(\Exception $ex) {}
    return $arr1;
}

function session_push(string $key, $val)
{
    if(is_null(session()->get($key)))
        session()->put($key, []); // create key if not exists
    session()->push($key, $val);
}

function session_push_alert(string $status, string $msg, string $alert_type = 'toastr')
{
    //bs_class => [ primary, secondary, success, danger, warning, info, light, dark ]
    $alert_types = ['swal2', 'toastr'];
    if(!in_array($alert_type, $alert_types))
        throw new exception('Invalid alert type `'.$alert_type.'`');
    $val = ['type' => $status, 'msg' => $msg];
    session_push(config('env.APP_SESSION_FEEDBACKS_KEY').'.'.$alert_type, $val);
}











/** -----------------------------------------------
 * STRING
 */

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
function str_sanitize(string $str, bool $one_space=true, bool $with_trim=true)
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

function str_random_alphanum(int $min_length = 10, int $max_length = 0) {
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
































/** -----------------------------------------------
 * VALIDATE
 */

function validate_simple(array $data, array $rules)
{
    return !Validator::make($data, $rules)->fails();  // LARAVEL DEPENDENT
}

function validate_url(string $url)
{
    return validate_simple(['ip' => $url], ['ip' => ['required', 'url']]);
}

function validate_ipv46(string $ipv46)
{
    return validate_simple(['ip' => $ipv46], ['ip' => 'required|ip']);
}

function validate_ipv4(string $ipv4)
{
    return validate_simple(['ipv4' => $ipv4], ['ipv4' => 'required|ipv4']);
}

function validate_ipv6(string $ipv6)
{
    return validate_simple(['ipv6' => $ipv6], ['ipv6' => 'required|ipv6']);
}















/** -----------------------------------------------
 * VAR
 */

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
    // try {
    //     return (string)$var;
    // } catch(\Exception $ex) {
    //     dump($var);
    //     dd($ex);
    // }
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
function view_variable(string $key, bool $strict=false)
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

/**
 * Same as `view_variable()`
 *
 * @param string $key
 * @param boolean $strict
 * @return mixed
 * @see `view_variable()`
 */
function vv(string $key, bool $strict=false)
{
    return view_variable($key, $strict);
}







function webclient_is_dev()
{
    return !empty(env('DEV_KEY')) && ((string)($_COOKIE['dev'] ?? '')) === env('DEV_KEY');
}

function webclient_timezone()
{
    return config('user.settings.timezone', 'Asia/Taipei');
}