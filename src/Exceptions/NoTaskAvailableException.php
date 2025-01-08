<?php

namespace App\Exceptions;

class NoTaskAvailableException extends \RuntimeException
{
    public const ERROR = 'No task available';
    public const MESSAGE = 'There is no task available';
}
