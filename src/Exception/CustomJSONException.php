<?php

namespace Rguj\Laracore\Exception;

use Throwable;
use Exception;

class CustomJSONException extends Exception
{
    protected bool $__assoc = false;

    /**
     * Json encodes the message and calls the parent constructor.
     *
     * @param null           $message
     * @param int            $code
     * @param Exception|null $previous
     * @param bool           $assoc
     */
    public function __construct($message = null, $code = 0, Exception $previous = null, bool $assoc = false)
    {
        $this->__assoc = $assoc;
        parent::__construct(json_encode($message), $code, $previous);
    }

    /**
     * Returns the json decoded message.
     *
     * @param bool $assoc
     *
     * @return mixed
     */
    public function getDecodedMessage()
    {
        return json_decode($this->getMessage(), $this->__assoc);
    }

}