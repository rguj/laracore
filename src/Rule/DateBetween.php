<?php

namespace Rguj\Laracore\Rule;

use Illuminate\Contracts\Validation\Rule;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\WebClient;
use Rguj\Laracore\Library\CLHF;
use Exception;

class DateBetween implements Rule
{
    public $data_rules;
    public $err_msg;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $data_rules)
    {
        $bool1 = (
            array_key_exists('min', $data_rules) && is_int($data_rules['min'])
            && array_key_exists('max', $data_rules) && is_int($data_rules['max'])
            && array_key_exists('regex', $data_rules) && is_string($data_rules['regex']) && AppFn::STR_IsBlankSpace($data_rules['regex']) !== true
            && array_key_exists('format_db', $data_rules) && is_string($data_rules['format_db']) && AppFn::STR_IsBlankSpace($data_rules['format_db']) !== true
            && array_key_exists('date_min', $data_rules) && is_string($data_rules['date_min']) && AppFn::STR_IsBlankSpace($data_rules['date_min']) !== true
            && array_key_exists('date_max', $data_rules) && is_string($data_rules['date_max']) && AppFn::STR_IsBlankSpace($data_rules['date_max']) !== true
        );
        if($bool1 !== true)
            throw new Exception('Invalid array structure');
        
        $this->data_rules = $data_rules;
        $this->err_msg = '';
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
        $client_tz = WebClient::getTimeZone();
        $dt_val = DT::createDateTimeUTC($value);
        $dt_min = DT::createDateTimeUTC($this->data_rules['date_min']);
        $dt_max = DT::createDateTimeUTC($this->data_rules['date_max']);

        $bool1 = (
            !AppFn::STR_IsBlankSpace($client_tz)
            && DT::isCarbonObject($dt_val)
            && DT::isCarbonObject($dt_min)
            && DT::isCarbonObject($dt_max)
        );
        
        if($bool1 !== true) {
            $this->err_msg = 'Invalid date.';
        } else {
            if($dt_val < $dt_min)
                $this->err_msg = 'Invalid date [-]';  
            else if($dt_val > $dt_max)
                $this->err_msg = 'Invalid date [+]';
        }
        
        if(!AppFn::STR_IsBlankSpace($this->err_msg))
            return false;
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->err_msg;
    }
}
