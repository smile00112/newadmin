<?php

namespace Webkul\TochkaPayment\Exceptions;

use Exception;

class InvalidRequestException extends Exception
{
    /**
     * HTTP status code for this exception.
     *
     * @var int
     */
    protected $code = 400;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @return void
     */
    public function __construct($message = 'Invalid request', $code = 400)
    {
        parent::__construct($message, $code);
    }
}
