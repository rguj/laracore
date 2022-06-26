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
    public array $firstErrors = [];
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
        // dd($this);

        return [
            // 'required'           => 'This is required',
            // 'present'            => 'This must be present',        
            // 'required_if'        => 'This is required',
            // 'required_without'   => 'This is required',

            // 'email'              => 'Invalid format',
            // 'array'              => 'Must be array',
            // 'integer'            => 'Must be integer',
            // 'string'             => 'Must be string',   

            // 'distinct'           => 'Must be distinct',    
            // 'min'                => 'Minimum of :min :character',
            // 'max'                => 'Exceeded :max :character',
            // 'regex'              => 'Invalid format',
            // 'exists'             => 'Invalid input',
            // 'date_format'        => 'Invalid date format',
            // 'in'                 => 'Invalid value',
            // 'not_in'             => 'Invalid value',


            // 'mimes'              => 'File type must be: :values',
            // 'file'               => 'Must be a file',
            // 'file.min'           => 'Minimum of :min KB',
            // 'file.max'           => 'Maximum of :max KB',
            // 'file.min_width'     => 'Minimum width is :min_width px',
            // 'file.max_width'     => 'Maximum width is :max_width px',
            // 'file.dimensions'     => 'Minimum dimension must be :dimensions',


            // all
            'accepted'         => 'The :attribute must be accepted.',
            'active_url'       => 'The :attribute is not a valid URL.',
            'after'            => 'The :attribute must be a date after :date.',
            'after_or_equal'   => 'The :attribute must be a date after or equal to :date.',
            'alpha'            => 'The :attribute may only contain letters.',
            'alpha_dash'       => 'The :attribute may only contain letters, numbers, and dashes.',
            'alpha_num'        => 'The :attribute may only contain letters and numbers.',
            'latin'            => 'The :attribute may only contain ISO basic Latin alphabet letters.',
            'latin_dash_space' => 'The :attribute may only contain ISO basic Latin alphabet letters, numbers, dashes, hyphens and spaces.',
            'array'            => 'Must be array.',
            'before'           => 'The :attribute must be a date before :date.',
            'before_or_equal'  => 'The :attribute must be a date before or equal to :date.',
            'between'          => [
                'numeric' => 'The :attribute must be between :min and :max.',
                'file'    => 'The :attribute must be between :min and :max kilobytes.',
                'string'  => 'The :attribute must be between :min and :max characters.',
                'array'   => 'The :attribute must have between :min and :max items.',
            ],
            'boolean'          => 'The :attribute field must be true or false.',
            'confirmed'        => 'The :attribute confirmation does not match.',
            'current_password' => 'The password is incorrect.',
            'date'             => 'The :attribute is not a valid date.',
            'date_equals'      => 'The :attribute must be a date equal to :date.',
            'date_format'      => 'Invalid date format. Must be (:format).',
            'different'        => 'The :attribute and :other must be different.',
            'digits'           => 'The :attribute must be :digits digits.',
            'digits_between'   => 'The :attribute must be between :min and :max digits.',
            'dimensions'       => 'Invalid image dimensions.',
            'distinct'         => 'Must be distinct.',
            'email'            => 'Invalid format.',
            'ends_with'        => 'The :attribute must end with one of the following: :values.',
            'exists'           => 'Invalid input',
            'file'             => 'Must be a file.',
            'filled'           => 'The :attribute field must have a value.',
            'gt'               => [
                'numeric' => 'The :attribute must be greater than :value.',
                'file'    => 'The :attribute must be greater than :value kilobytes.',
                'string'  => 'The :attribute must be greater than :value characters.',
                'array'   => 'The :attribute must have more than :value items.',
            ],
            'gte' => [
                'numeric' => 'The :attribute must be greater than or equal :value.',
                'file'    => 'The :attribute must be greater than or equal :value kilobytes.',
                'string'  => 'The :attribute must be greater than or equal :value characters.',
                'array'   => 'The :attribute must have :value items or more.',
            ],
            'image'    => 'The :attribute must be an image.',
            'in'       => 'Invalid value.',
            'in_array' => 'Invalid value.', //'The :attribute field does not exist in :other.',
            'integer'  => 'Must be integer.',
            'ip'       => 'The :attribute must be a valid IP address.',
            'ipv4'     => 'The :attribute must be a valid IPv4 address.',
            'ipv6'     => 'The :attribute must be a valid IPv6 address.',
            'json'     => 'The :attribute must be a valid JSON string.',
            'lt'       => [
                'numeric' => 'The :attribute must be less than :value.',
                'file'    => 'The :attribute must be less than :value kilobytes.',
                'string'  => 'The :attribute must be less than :value characters.',
                'array'   => 'The :attribute must have less than :value items.',
            ],
            'lte' => [
                'numeric' => 'The :attribute must be less than or equal :value.',
                'file'    => 'The :attribute must be less than or equal :value kilobytes.',
                'string'  => 'The :attribute must be less than or equal :value characters.',
                'array'   => 'The :attribute must not have more than :value items.',
            ],
            'max' => [
                'numeric' => 'The :attribute may not be greater than :max.',
                'file'    => 'Maximum of :max KB.',
                'string'  => 'Exceeded :max :character.',
                'array'   => 'The :attribute may not have more than :max items.',
            ],
            'mimes'     => 'File type must be: :values.',
            'mimetypes' => 'The :attribute must be a file of type: :values.',
            'min'       => [
                'numeric' => 'The :attribute must be at least :min.',
                'file'    => 'Minimum of :min KB.',
                'string'  => 'Minimum of :min :character.',
                'array'   => 'The :attribute must have at least :min items.',
            ],
            'not_in'               => 'Invalid value.',
            'not_regex'            => 'Invalid format.',
            'numeric'              => 'The :attribute must be a number.',
            'password'             => 'The password is incorrect.',
            'present'              => 'This must be present.',
            'regex'                => 'Invalid format.',
            'required'             => 'This is required.',
            'required_if'          => 'This is required.',
            'required_unless'      => 'This is required.',
            'required_with'        => 'This is required.',
            'required_with_all'    => 'This is required.',
            'required_without'     => 'This is required.',
            'required_without_all' => 'This is required.',
            'same'                 => 'The :attribute and :other must match.',
            'size'                 => [
                'numeric' => 'The :attribute must be :size.',
                'file'    => 'The :attribute must be :size kilobytes.',
                'string'  => 'The :attribute must be :size characters.',
                'array'   => 'The :attribute must contain :size items.',
            ],
            'starts_with' => 'The :attribute must start with one of the following: :values.',
            'string'      => 'Must be string.',
            'timezone'    => 'The :attribute must be a valid zone.',
            'unique'      => 'The :attribute has already been taken.',
            'uploaded'    => 'The :attribute failed to upload.',
            'url'         => 'The :attribute format is invalid.',
            'uuid'        => 'The :attribute must be a valid UUID.',
            'custom'      => [
                'attribute-name' => [
                    'rule-name' => 'custom-message',
                ],
            ],
            'reserved_word'                  => 'The :attribute contains reserved word',
            'dont_allow_first_letter_number' => 'The \":input\" field can\'t have first letter as a number',
            'exceeds_maximum_number'         => 'The :attribute exceeds maximum model length',
            'db_column'                      => 'The :attribute may only contain ISO basic Latin alphabet letters, numbers, dash and cannot start with number.',
            'attributes'                     => [],

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
        $this->forcedError = true;
        $this->customErrorMessages[] = $err_msg;
    }

    final public function hasError()
    {
        return !empty($this->customErrorMessages) || $this->forcedError || $this->validator->fails();
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
