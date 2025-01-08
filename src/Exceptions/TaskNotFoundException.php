<?php

namespace App\Exceptions;

use Exception;

class TaskNotFoundException extends Exception
{

    const ERROR = 'Not Found';
    const MESSAGE = 'The requested task could not be found.';

}