<?php

namespace Webkul\TochkaPayment\Exceptions;

use Exception;

class InvalidSignatureException extends Exception
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $code = 401;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct($message = 'Invalid webhook signature', $code = 401, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
