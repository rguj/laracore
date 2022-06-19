<?php

namespace Rguj\Laracore\Rule;

use Illuminate\Contracts\Validation\Rule;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\WebClient;
use Rguj\Laracore\Library\CLHF;
use Exception;

use Carbon\Carbon;
use Carbon\CarbonImmutable;


class DateBetween2 implements Rule
{
    private Carbon $d1;
    private Carbon $d2;
    private Carbon $v;
    private bool $startOfDay = false;
    private string $format;
    private string $format2;
    private string $err_msg;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $date_min, string $date_max, string $format, string $timezone, bool $startOfDay)
    {
        $format2 = $format.' H:i:s.u';
        $d1 = dt_parse($date_min.' 00:00:00.000000', [$format2, dt_standard_format()], [webclient_timezone(), 'UTC']);
        $d2 = dt_parse($date_max.' 23:59:59.999999', [$format2, dt_standard_format()], [webclient_timezone(), 'UTC']);
        
        if(!$d1->is_valid) throw new exception('Invalid format: date_min');
        if(!$d2->is_valid) throw new exception('Invalid format: date_max');
        if($d2->carbon->onto < $d1->carbon->onto) throw new exception('date_max must be greater than date_min');

        $this->d1 = $d1->carbon->onto;
        $this->d2 = $d2->carbon->onto;
        $this->format = $format;
        $this->format2 = $format2;
        $this->startOfDay = $startOfDay;
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
        $client_tz = webclient_timezone();
        if(empty($client_tz))
            throw new exception('Invalid client timezone');

        // $v = DT::createDateTimeX2($value, [$this->format, $this->format], [$client_tz, 'UTC'], false);

        // this assumes that dt_str is already parse to the standard timezone
        $v = dt_parse($value, [$this->format2, $this->format2], ['UTC', 'UTC']);

        if(!$v->is_valid) {
            $this->err_msg = 'Invalid date.';  // invalid value date format
            return false;
        }
        $this->v = $v->carbon->onto;

        if($this->v < $this->d1)
            $this->err_msg = 'Invalid date [-]';
        else if($this->v > $this->d2)
            $this->err_msg = 'Invalid date [+]';
        
        return !(!empty($this->err_msg));
            return false;
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
