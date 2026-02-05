<?php

namespace Webkul\TochkaPayment\Exceptions;

use Exception;

class InvalidSignatureException extends Exception
{
    /**
     * HTTP status code for this exception.
     *
     * @var int
     */
    protected $code = 403;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @return void
     */
    public function __construct($message = 'Invalid signature', $code = 403)
    {
        parent::__construct($message, $code);
    }
}
