<?php

namespace App\Exceptions;

use Exception;

class ExternalResourceNotFoundException extends Exception
{
    public function __construct(string $message = 'External resource not found', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}