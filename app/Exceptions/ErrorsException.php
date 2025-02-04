<?php

namespace App\Exceptions;

use Exception;

class ErrorsException extends Exception
{
    public function __construct($message = 'Có một lỗi không mong muốn.', $code = 400)
    {
        parent::__construct($message, $code);
    }
}