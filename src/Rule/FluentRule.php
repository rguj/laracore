<?php
namespace Rguj\Laracore\Rule;

use Exception;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Fluent Rule - chain methods to create rules array
 */
class FluentRule {
    

    // (ro) rule order

    private array $ro1_primary = [
        'present',
        'required',
    ];

    private array $ro2_type = [
        'string',
        'integer',
        'array',
    ];

    private array $ro3_any = [
        'exists',
        'min',
        'max',
        'regex',
        'distinct',
    ];

    private string $key = '';
    private string $sameAs = '';
    private array $data_sameAs = [];
    private array $data1_primary = [];
    private array $data2_type = [];
    private array $data3_any = [];
    private array $data4_customRules = [];

    private int $min = 0;
    private int $max = 0;


    private $create;
    private $queries = [];
    private $inputs = [];
    private $files = [];
    private $all = [];
    private $request;
    private $baseRequest;
    private array $genericRule = [];
    private array $classParents = [];
    private array $mustClassParents = [];


    /**
     * Construct `make()` or `key()`
     *
     * @param \App\Http\Requests\Request|null $request
     * @param string|null $key
     */
    public function __construct($request = null, $key = null, array $genericRule = [])
    {
        if(!is_null($request)) {
            $this->genericRule = !empty($genericRule) ? $genericRule : [];
            $this->mustClassParents = [
                'Symfony\Component\HttpFoundation\Request',
            ];
            $this->classParents = class_parents($request);
            $found = false;
            foreach($this->classParents as $k=>$v) {
                if(in_array($v, $this->mustClassParents)) {
                    $found = true;
                    break;
                }
            }
            if(!$found)
                throw new exception('$request instance is invalid');

            // $this->request = $request;
            $this->baseRequest = $request;

            $this->queries = (array)$request->query();
            $this->inputs = (array)$request->input();
            $this->files = (array)$request->file();
            $this->all = array_merge($this->queries, $this->inputs, $this->files);
        }        
        elseif(!is_null($key)) {
            if(!is_string($key))
                throw new exception('$key must be string');
            $this->key = $key;
        }
        else {
            throw new exception('All parameters are null. At least one must not be null.');
        }

    }

    final private function __resetData()
    {
        $this->key = '';
        $this->data1_primary = [];
        $this->data2_type = [];
        $this->data3_any = [];
        $this->data4_customRules = [];
    }

    final private function __joiner(string $rule, $params)
    {
        if(!(is_string($params) || is_array($params)))
            throw new exception('$params must be string or array');

        $rule = rtrim(trim($rule), ':');
        $params = is_string($params) ? ltrim(trim($params), ':') : $params;
        $p = $params;

        return $rule.(!empty($p) ? ':'.implode(',', $p) : '');
    }

    final private function __splitter(string $rule)
    {
        $pcs = explode(':', $rule, 2);
        $rule = trim((string)($pcs[0] ?? ''));
        $p = trim((string)($pcs[1] ?? ''));
        $params = (!empty($p) || $p === '0') ? ($rule === 'regex' ? [$p] : explode(',', $p)) : [];
        return [$rule, $params];
    }

    final private function __getData(bool $hasParentRule)
    {
        $this->__eval();
        $arr = [];

        foreach($this->ro1_primary as $k=>$v) {
            if(!array_key_exists($v, $this->data1_primary))
                continue;        
            $arr[] = $this->__joiner($v, $this->data1_primary[$v]);
        }
        foreach($this->ro2_type as $k=>$v) {
            if(!array_key_exists($v, $this->data2_type))
                continue;
            $arr[] = $this->__joiner($v, $this->data2_type[$v]);            
        }
        foreach($this->data3_any as $k=>$v) {
            // $v2 = $hasParentRule ? $this->getRule($this->key.'.'.$k) : $v;
            // $v2 = $hasParentRule ? $this->genericRule[$this->key.'.'.$k] : $v;
            $v2 = $hasParentRule ? config('rules.generic.'.$this->key.'.'.$k) : $v;
            $arr[] = $this->__joiner($k, $v2);
        }
        foreach($this->data4_customRules as $k=>$v) {
            $arr[] = $v;
        }

        return $arr;
    }

    final private function __eval()
    {
        if(empty($this->data1_primary))
            throw new exception('Data 1 is empty (primary)');
        if(empty($this->data2_type))
            throw new exception('Data 2 is empty (type)');

        if($this->min < 0)
            throw new exception('Minimum value must be zero and above');
        if(array_key_exists('max', $this->data3_any) && $this->max < $this->min)
            throw new exception('Max value must be greater than min value (given '.$this->min.')');
    }

    final private function __getModelTable(string $modelOrTable)
    {
        if(class_exists($modelOrTable)) {
            $parent = 'Illuminate\Database\Eloquent\Model';
            if(!array_key_exists($parent, class_parents($modelOrTable)))
                throw new exception('Missing class\' parent `'.$parent.'` of the given `'.$modelOrTable.'`');
            return $modelOrTable::__callStatic('getTable', []);
        }
        return $modelOrTable;
    }









    final public static function factory($request, array $genericRule = [])
    {
        return (new static($request, null, $genericRule));
    }

    final public function make(array $arr, bool $strict = false)
    {
        return $this->create($arr,  $strict);
    }

    final public function create(array $arr, bool $strict = false)
    {
        $arr2 = [];
        /** @var \App\Rules\Core\FluentRule $v */
        foreach($arr as $k=>$v) {
            if(!empty($v->sameAs)) {
                if(!array_key_exists($v->sameAs, $arr2))
                    throw new exception('sameAs key ('.$v->sameAs.') not found on the create list');
                $arr2[$v->key] = $arr2[$v->sameAs];
            } else {
                if($strict && !array_key_exists($v->key, $this->all)) {  // validate keys
                    throw new exception('Key not found: '.$k);
                }
                $v->baseRequest = $this->baseRequest;
                $arr2[$v->key] = $v->get();
            }
        }
        $this->create = $arr2;

        return $this;
    }

    final public function getArr()
    {
        return $this->create;
    }

    final public function dd()
    {
        dd($this->create);
    }

    final public static function key(string $key)
    {
        $key = trim($key);
        if(empty($key)) throw new Exception('$key is empty');
        return new static(null, $key);
    }
    
    final public function sameAs(string $key)
    {
        $this->sameAs = $key;

        return $this;
    }
    
    final public function get(bool $hasParentRule = false, bool $resetData = false)
    {
        $data = $this->__getData($hasParentRule);
        if($resetData) $this->__resetData();
        return $data;
    }

    final public function addCustomRule(string $rulepath, array $args = [])
    {
        $rulepath = Str::replace('/', '\\', trim($rulepath));
        $rulepath = (!Str::startsWith($rulepath, '\\') ? '\\' : '').$rulepath;
        $cname = "\App\Rules".$rulepath;
        if(!class_exists($cname))
            throw new exception('Class doesn\'t exists: '.$cname);
        $cls1 = new $cname(...$args);

        $this->data4_customRules[] = $cls1;
    }

    /**
     * Add abstract rule
     *
     * @param string $rule
     * @param null|string|int $params
     * @param null|int $orderIndex
     * @param boolean $findRule
     * @return void
     */
    final private function addRule(string $rule, $params, $orderIndex = null, bool $findRule = false)
    {
        if(!(is_null($params) || is_string($params) || is_int($params)))
            throw new exception('$params must be null, string, or int');
        $p = $params;
        $params = (string)$params;
        list($rule, $params) = $this->__splitter($rule.(!empty($params) ? ':'.$params : ''));
        $ruleFinal = $this->__joiner($rule, $params);

        if(!(is_int($orderIndex) || is_null($orderIndex)))
            throw new exception('$orderIndex must be null or int');

        if(is_int($orderIndex)) {
            switch($orderIndex) {
                case 0:
                    if(!in_array($rule, $this->ro1_primary, true))
                        throw new Exception('Value not found: '.$orderIndex.'.'.$rule);
                    $this->data1_primary = [$rule => $params];
                    break;                
                case 1:
                    if(!in_array($rule, $this->ro2_type, true))
                        throw new Exception('Value not found: '.$orderIndex.'.'.$rule);
                    $this->data2_type = [$rule => $params];
                    break;
            }
        }
        elseif(is_null($orderIndex)) {
            if(!in_array($rule, $this->ro3_any, true))
                throw new Exception('Value not found (any): '.$rule);

            if($findRule && is_null($p)) {
                $r = config('rules.generic.'.$this->key.'.'.$rule);
                switch($r) {
                    case 'min': $this->min = (int)$r; break;
                    case 'max': $this->max = (int)$r; break;
                }
                $this->data3_any[$rule] = [$r];
            } else {
                $this->data3_any[$rule] = $params;
            }
        }

        point1:
    }








    // PRIMARIES

    final public function present()
    {
        $this->addRule('present', null, 0, false);
        
        return $this;
    }

    final public function required()
    {
        $this->addRule('required', null, 0, false);

        return $this;
    }


    final public function requiredIf(string $key, string $value)
    {
        $this->__evalType($key, ['str'], 'key', true);
        $this->__evalType($value, ['str'], 'value', true);
        $this->addRule('required_if', [$key, $value], 0, false);

        return $this;
    }


    final public function requiredOrPresent(bool $cond)
    {
        if($cond) $this->required();
        else      $this->present();
        
        return $this;
    }





    // SECONDARIES

    final public function str()
    {
        $this->addRule('string', null, 1, false);
        
        return $this;
    }

    final public function int()
    {
        $this->addRule('integer', null, 1, false);
        
        return $this;
    }

    final public function arr()
    {
        $this->addRule('array', null, 1, false);
        
        return $this;
    }
    
    final public function date()
    {
        $this->addRule('date', null, 1, false);
        
        return $this;
    }






    // ANY

    final private function __dtBetween(string $date1, string $date2, string $format, string $timezone, bool $startOfDay = false)
    {
        $this->addCustomRule('DateBetween2', func_get_args());
    }

    final public function dateXBetween(string $date1, string $date2 = '', string $format = '', string $timezone = 'UTC')
    {
        $date2 = empty($date2) ? Carbon::now($timezone)->startOfDay()->format($format) : $date2;
        $this->__dtBetween($date1, $date2, $format, $timezone);

        return $this;
    }

    /**
     * Adds a callable rule to check the date range
     *
     * @param string $date1
     * @param string $date2 default `now()`
     * @param string $timezone
     * @return $this
     */
    final public function dateBetween(string $date1, string $date2 = '', string $timezone = 'UTC')
    {
        $format = 'Y-m-d';
        $timezone = empty($timezone) ? 'UTC' : $timezone;
        $date2 = empty($date2) ? Carbon::now($timezone)->format($format) : $date2;
        $this->__dtBetween($date1, $date2, $format, $timezone, false);

        return $this;
    }

    final public function dateTimeBetween(string $date1, string $date2 = '', string $timezone = 'UTC')
    {
        $format = 'Y-m-d H:i:s.u';
        $timezone = empty($timezone) ? 'UTC' : $timezone;
        $date2 = empty($date2) ? Carbon::now($timezone)->format($format) : $date2;
        $this->__dtBetween($date1, $date2, $format, $timezone, false);

        return $this;
    }

    /**
     * Adds a min rule
     *
     * @param null|string $min
     * @return $this
     */
    final public function min($min = null)
    {
        $this->__evalType($min, ['null', 'integer'], 'min');
        $this->addRule('min', $min, null, is_null($min));

        return $this;
    }

    /**
     * Adds a max rule
     *
     * @param null|string $max
     * @return $this
     */
    final public function max($max = null)
    {
        $this->__evalType($max, ['null', 'integer'], 'max');
        $this->addRule('max', $max, null, is_null($max));

        return $this;
    }

    /**
     * Adds rule to check if value exists in database
     *
     * @param string $modelOrTable `column|table`
     * @param null|string $column
     * @return $this
     */
    final public function exists(string $modelOrTable, $column = null)
    {
        $this->__evalType($column, ['null', 'string'], 'column');

        if(empty($modelOrTable))
            throw new exception('$modelOrTable must be filled');
        $table = $this->__getModelTable($modelOrTable);
        if(empty($table))
            throw new exception('$table is empty');
        // $k = 'exists:'.$table;
        $k = $table;
        if(is_string($column)) {
            if(empty($column))
                throw new exception('$column must be a filled string');
            $k .= ','.$column;
        }
        $this->addRule('exists', $k, null, false);

        return $this;
    }

    /**
     * Adds regex rule
     *
     * @param null|string $regex
     * @return $this
     */
    final public function regex($regex = null)
    {
        $this->__evalType($regex, ['null', 'string'], 'regex');
        $this->addRule('regex', $regex, null, is_null($regex));

        return $this;
    }

    /**
     * Adds email rule (regex)
     *
     * @param null|string $regex
     * @return $this
     */
    final public function email($regex = null)
    {
        $this->__evalType($regex, ['null', 'string'], 'regex');
        $this->addRule('regex', $regex, null, is_null($regex));

        return $this;
    }

    /**
     * Adds distinct rule
     *
     * @return $this
     */
    final public function distinct()
    {
        $this->addRule('distinct', null, null, false);

        return $this;
    }

    /**
     * Evaluates data type
     *
     * @param mixed $var
     * @param string|array $type
     * @param string $varname
     * @return void
     * @throws Exception
     */
    final private function __evalType($var, $type, string $varname = '', bool $notEmpty = false)
    {
        if(!in_array(gettype($type), ['string', 'array'], true))
            throw new exception('$type must be string or array');

        $t = is_string($type) ? explode('|', trim($type)) : $type;
        $overrides = [
            'int' => 'integer',
            'null' => 'NULL',
            'str' => 'string',
            'bool' => 'boolean',
        ];
        foreach($t as $k=>$v) {
            $t[$k] = array_key_exists($v, $overrides) ? $overrides[$v] : $v;
        }
        if(empty($t))
            throw new exception('$type must not be empty');

        $v = empty($varname) ? '$var' : $varname;
        $v = !Str::startsWith($v, '$') ? '$'.$v : $v;

        if(!in_array(gettype($var), $t))
            throw new exception($v.' must be these types: '.strtolower(implode(', ', $t)));

        if($notEmpty && empty($var))
            throw new exception($v.' must not be empty');
    }





}