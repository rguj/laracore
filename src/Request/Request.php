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

	final protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->validator = $validator;

        // implement error report logic below

        if(!$this->ajax()) {
            return redirect()->back()->withErrors($validator->getMessageBag()->getMessages());
        }

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
     * Creates attribute for non-existent input based from rules
     *
     * @return void
     */
    final public function fillMissingInputs()
    {
        // create empty element if not exists
        $all = $this->all();
        foreach($this->rules() as $k=>$v) {
            if(array_key_exists($k, $all))
                continue;
            $all[$k] = null;
        }
        $this->merge($all);
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




}
