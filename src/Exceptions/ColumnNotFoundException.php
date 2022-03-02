<?php

namespace JocelimJr\LumenDTSS\Exceptions;

use Exception;

class ColumnNotFoundException extends Exception
{
    public function __construct($columnName)
    {
        parent::__construct("Column '$columnName' not found", 400);
    }
}
