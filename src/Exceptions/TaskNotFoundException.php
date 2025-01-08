<?php

namespace App\Exceptions;

class TaskNotFoundException extends \Exception
{
    public const ERROR = 'Not Found';
    public const MESSAGE = 'The requested task could not be found.';
}
