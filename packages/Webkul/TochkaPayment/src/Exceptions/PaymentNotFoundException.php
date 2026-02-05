<?php

namespace Webkul\TochkaPayment\Exceptions;

use Exception;

class PaymentNotFoundException extends Exception
{
    /**
     * HTTP status code for this exception.
     *
     * @var int
     */
    protected $code = 404;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @return void
     */
    public function __construct($message = 'Payment not found', $code = 404)
    {
        parent::__construct($message, $code);
    }
}
