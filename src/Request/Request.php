<?php
namespace Rguj\Library\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Exception;

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


    public function __construct()
    {
        $request = request();

        $this->genericRuleClass = new GenericRule();
        // $this->genericRuleClass->finalizeRules();
        $this->genericRule = $this->genericRuleClass->getRules();
        config()->set('rules.generic', $this->genericRule);

        $this->fluentRule = FR::factory($request, $this->genericRule);
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
        $controller = explode('@', $route->action['controller']);
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




}
