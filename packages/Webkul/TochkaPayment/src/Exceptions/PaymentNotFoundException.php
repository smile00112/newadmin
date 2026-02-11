<?php

namespace Webkul\TochkaPayment\Exceptions;

use Exception;

class PaymentNotFoundException extends Exception
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $code = 404;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct($message = 'Payment not found', $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
