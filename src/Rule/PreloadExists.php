<?php

namespace Rguj\Laracore\Rule;

use Illuminate\Contracts\Validation\Rule;

use Rguj\Laracore\Library\AppFn;
use Rguj\Laracore\Library\DT;
use Rguj\Laracore\Library\WebClient;
use Rguj\Laracore\Library\CLHF;
use Exception;

class PreloadExists implements Rule
{
    public $tbl;
    public $col;
    public $is_valid;
    //public $err_type;


    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $tbl, string $col, int $is_valid=1)
    {
        //
        $this->tbl = $tbl;
        $this->col = $col;
        $this->is_valid = $is_valid;
        //$this->err_type = '';
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
        $el = [$attribute, $value];
        $db = [$this->tbl, $this->col, $this->is_valid];
        $vld = CLHF::DB_PreloadExists($el, $db);
        return $vld[0] === true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid data.';
    }
}
