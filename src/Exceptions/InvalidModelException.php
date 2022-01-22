<?php

namespace JocelimJr\LumenDTSS\Exceptions;

use Exception;

class InvalidModelException extends Exception
{
    public function __construct($method = null, $code = 500, \Throwable $previous = null)
    {
        parent::__construct("Invalid model class ($method)", $code, $previous);
    }
}
