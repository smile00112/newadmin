<?php

namespace Webkul\TochkaPayment\Exceptions;

use Exception;

class InvalidRequestException extends Exception
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $code = 400;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct($message = 'Invalid request', $code = 400, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
