<?php
namespace Rguj\Laracore\Request;

use Exception;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Rguj\Laracore\Rule\GenericRule;
use Rguj\Laracore\Rule\FluentRule;

class Request extends FormRequest
{

    // public const REQUEST_DECRYPT = [
    //     'index' => array|string,
    //     'show',
    //     'create',
    //     'store',
    //     'edit',
    //     'update',
    //     'destroy',
    //     'massDestroy',
    // ];
	
	public $genericRuleClass;
	public array $genericRule = [];
	public $fluentRule;

    public $validator;
    public array $translated = [];
    public array $firstErrors;
    public bool $forcedError = false;
    public array $customErrorMessages = [];


    public function __construct()
    {
        $request = request();

        $this->genericRuleClass = new GenericRule();
        $this->genericRule = $this->genericRuleClass->getRules();
        config()->set('rules.generic', $this->genericRule);

        $this->fluentRule = FluentRule::factory($request, $this->genericRule);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Prepare data for validation.
     * 
     * - please invoke `parent::prepareForValidation()`
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $this->decryptElements();
    }

	// final protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
	final public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->validator = $validator;
        $f = [];
        foreach($validator->errors()->getMessageBag()->getMessages() as $k=>$v) {
            if(!empty($v)) {
                $f[$k] = $v[0];
            }
        }
        $this->firstErrors = $f;
        // $this->flash();

        // implement error report logic below

        if(!$this->ajax()) {
            // return redirect()->back()->withErrors($validator->getMessageBag()->getMessages());
        }

    }

    public function passedValidation()
    {
        // session()->remove('_old_input');
    }
	
	/**
     * Get custom messages for validator errors.
     * 
     * - please invoke `parent::messages()`
     *
     * @return void
     */
    public function messages()
    {
        return [
            'required'           => 'This is required',
            'present'            => 'This must be present',        
            'required_if'        => 'This is required',
            'required_without'   => 'This is required',

            'email'              => 'Invalid format',
            'array'              => 'Must be array',
            'integer'            => 'Must be integer',
            'string'             => 'Must be string',   

            'distinct'           => 'Must be distinct',    
            'min'                => 'Minimum of :min :character',
            'max'                => 'Exceeded :max :character',
            'regex'              => 'Invalid format',
            'exists'             => 'Invalid input',
            'date_format'        => 'Invalid date format',
            'in'                 => 'Invalid value',
            'not_in'             => 'Invalid value',
        ];
    }







	// -----------------------------------------
	// CUSTOM FUNCTIONS	

    final public function getCustomErrorMessages()
    {
        $e = [];
        foreach($this->customErrorMessages as $k=>$v) {
            $e[] = ['error', $v];
        }
        return $e;
    }

    final public function addCustomErrorMessage(string $err_msg)
    {
        $this->customErrorMessages[] = $err_msg;
    }

    final public function hasError()
    {
        return $this->forcedError || $this->validator->fails();
    }

    final public function decryptElements()
    {
        $de = $this->getDecryptElements();
        foreach($de as $k=>$v) {
            if(!$this->has($v)) continue;
            $crypt = crypt_de($this->get($v));
            $this->merge([$v=>$crypt]);
        }
    }

    final public function getDecryptElements()
    {
        $arrDecrypt = [];
        $route = Route::getCurrentRoute();
        $action = $route->action;
        if(!array_key_exists('controller', $action)) goto point1;
        $controller = explode('@', $action['controller']);
        $b = (
            !empty($action)
            && array_key_exists('as', $action) && !empty($action['as']) && !Str::startsWith($action['as'], 'generated::')
            && method_exists(...$controller)
        );
        if(!$b) goto point1;
        try { $arrDecrypt = $controller[0]::REQUEST_DECRYPT[$controller[1]]; } catch(\Throwable $ex) {}
        $arrDecrypt = is_string($arrDecrypt) ? explode('|', trim($arrDecrypt)) : $arrDecrypt;
        if(!in_array(gettype($arrDecrypt), ['string', 'array']))
            throw new Exception('Invalid type: '.basename($controller[0]).'::REQUEST_DECRYPT.'.$controller[1].'. Must only be string or array.');
        if(!empty($arrDecrypt) && !arr_type_seq($arrDecrypt))
            throw new Exception('$arrDecrypt must be a sequential array');
        $arr = [];
        foreach($arrDecrypt as $k=>$v) {  // trim and remove empty strings
            $v = trim($v);
            if(empty($v)) continue;
            $arr[] = $v;
        }
        $arrDecrypt = $arr;
        point1:
        return $arrDecrypt;
    }

    /**
     * Translates input to database column key `_id`
     * 
     * only use this on method `passedValidation()`
     *
     * @param array $arr [ `attr`, `table`, `column`, `override_new_attr`, `mergeNeedle` ]
     * @return void|array
     */
    final public function translate(array $arr, bool $update = true, array $thisInputs = [])
    {
        $opt = [];

        if(!empty($thisInputs)) {
            $update = false;
            $inputs = $thisInputs;
        } else {
            $inputs = $this->all();
        }

        // dump($inputs);
        // $rules = $this->rules();
        foreach($arr as $k=>$v) {
            list($attr, $table, $column) = $v;

            if(!array_key_exists($attr, $inputs))
                throw new exception('Attribute not found: '.$attr);
            if(empty($table))
                throw new exception('Empty table on attribute: '.$attr);

            $new_attr = str_sanitize($v[3] ?? '');
            $new_attr = !empty($new_attr) ? $new_attr : $attr.'_id';

            $column = empty($column) ? $attr : $column;
            $mergeNeedle = (array)($v[4] ?? []);

            if(is_array($inputs[$attr])) {
                $d = [];
                foreach($inputs[$attr] as $k3=>$v3) {
                    // $mergeNeedle = (array)($v[4] ?? []);
                    $needle = [$column=>$v3];
                    if(!empty($mergeNeedle))
                        $needle = array_merge($mergeNeedle, $needle);
                    $d[] = db_cache_fetsert_id($table, $needle, true);
                }
                $d = array_unique($d);
                $opt[$new_attr] = $d;
            } else {
                // $mergeNeedle = (array)($v[4] ?? []);
                $needle = [$column=>$inputs[$attr]];
                if(!empty($mergeNeedle))
                    $needle = array_merge($mergeNeedle, $needle);    
                $opt[$new_attr] = db_cache_fetsert_id($table, $needle, true);
            }
        }
        // dd($opt);
        if($update)
            $this->translated = $opt;
        else return $opt;
    }

    final public function getTranslated()
    {
        return $this->translated;
    }

    /**
     * Creates attribute for non-existent input based from rules
     * 
     * this follows the order of rule keys
     *
     * @return void
     */
    final public function fillMissingInputs()
    {
        $all = $this->all();
        $all2 = [];
        foreach($this->rules() as $k=>$v) {
            if(Str::endsWith($k, '.*')) continue;
            $v2 = array_key_exists($k, $all) ? $all[$k] : null;
            $v2 = !is_null($v2) ? $v2 : (Str::endsWith($k, '.*') ? [] : '');
            $all2[$k] = $v2;
        }
        $this->merge($all2);
    }

    /**
     * Converts the date format (from client to server)
     *
     * @param array $keys
     * @return void
     */
    final public function convertDateFormat(array $keys)
    {
        $all = $this->all();
        foreach($keys as $k=>$v) {
            if(
                array_key_exists($v, $all)
                && !is_null($all[$v])
                && is_callable(arr_get($this->genericRule, $v.'.converter', null))
            ) {
                $all[$v] = $this->genericRule[$v]['converter']($all[$v]);
            }
        }
        $this->merge($all);
    }

    /**
     * Converts the mobile number (from client to server)
     *
     * @param array $keys
     * @return void
     */
    final public function convertMobileNum(array $keys)
    {
        $all = $this->all();
        foreach($keys as $k=>$v) {
            if(
                array_key_exists($v, $all)
                && !is_null($all[$v])
                && !empty(arr_get($this->genericRule, $v.'.prefix', ''))
            ) {
                if(!empty($all[$v]))
                    $all[$v] = $this->genericRule[$v]['prefix'].$all[$v];
            }
        }
        $this->merge($all);
    }




}
