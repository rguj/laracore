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
    private string $err_msg;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $date_min, string $date_max, string $format, string $timezone, bool $startOfDay)
    {
        $d1 = DT::createDateTimeX2($date_min, [$format, $format], [$timezone, 'UTC'], $startOfDay);
        $d2 = DT::createDateTimeX2($date_max, [$format, $format], [$timezone, 'UTC'], $startOfDay);

        if(!$d1[0]) throw new exception('Invalid format: date_min');
        if(!$d2[0]) throw new exception('Invalid format: date_max');
        if($d2[3][1] < $d1[3][1]) throw new exception('date_max must be greater that dadate_minte1');

        $this->d1 = $d1[3][1];
        $this->d2 = $d2[3][1];
        $this->format = $format;
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

        $v = DT::createDateTimeX2($value, [$this->format, $this->format], [$client_tz, 'UTC'], false);
        if(!$v[0]) {
            $this->err_msg = 'Invalid date.';  // invalid value date format
            return false;
        }
        $this->v = $v[3][1];

        if($this->v < $this->d1)
            $this->err_msg = 'Invalid date [-]';
        else if($this->v > $this->d2)
            $this->err_msg = 'Invalid date [+]';
        
        if(!empty($this->err_msg))
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
