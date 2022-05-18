<?php

namespace Rguj\Laracore\Rule;

use Exception;
use Illuminate\Contracts\Validation\Rule;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\WebClient;
use Rguj\Laracore\Library\CLHF;

class MinMaxRegex implements Rule
{
    public $min;
    public $max;
    public $regex;
    // public $err_type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $args = func_get_args();
        $args_c = count($args);
        
        if($args_c === 1) {
            $arg1 = $args[0];
            $bool1 = (
                is_array($arg1) && AppFn::ARRAY_IsTypeAssociative($arg1) === true
                && array_key_exists('min', $arg1) && is_int($arg1['min'])
                && array_key_exists('max', $arg1) && is_int($arg1['max'])
                && array_key_exists('regex', $arg1) && is_string($arg1['regex'])
            );
            if($bool1 !== true)
                throw new exception('Invalid array structure of parameter 1');
            $min = $arg1['min'];
            $max = $arg1['max'];
            $regex = $arg1['regex'];
        }
        else if($args_c === 3 ) {
            if(!is_int($args[0]))
                throw new exception('Parameter 1 must be integer');
            if(!is_int($args[1]))
                throw new exception('Parameter 2 must be integer');
            if(!is_string($args[2]))
                throw new exception('Parameter 3 must be string');
            $min = $args[0];
            $max = $args[1];
            $regex = $args[2];

        }
        else {
            throw new Exception('Invalid argument/s');
        }

        if($min < 0)
            throw new Exception('$min must be zero or greater');
        if($max < 0)
            throw new Exception('$max must be zero or greater');
        if($min > $max)
            throw new Exception('$min is greater than $max');   
        // if(AppFn::STR_IsBlankSpace($regex))
        //     throw new Exception('$regex is empty');

        $this->min = $min;
        $this->max = $max;
        $this->regex = trim($regex);
        $this->err_type = '';      
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        if(strlen($value) < $this->min) {
            $this->err_type = 'min';
            return false;
        }
        if(strlen($value) > $this->max) {
            $this->err_type = 'max';
            return false;
        }
        if(!empty($this->regex) && AppFn::STR_preg_match($this->regex, $value) !== true) {
            $this->err_type = 'regex';
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        //return 'The validation error message.';
        $sfx_min = $this->min > 1 ? 's' : '';
        $sfx_max = $this->max > 1 ? 's' : '';
        switch($this->err_type) {
            case 'min':
                return 'Minimum of '.$this->min.' character'.$sfx_min.'.';
                break;            
            case 'max':
                return 'Exceeded '.$this->max.' character'.$sfx_max.'.';
                break;            
            case 'regex':
                return 'Invalid format.';
                break;
            default:
                return 'Unknown error';
                break;
        }
    }
}
