<?php

namespace App\Exceptions;

use RuntimeException;

class NoTaskAvailableException extends RuntimeException
{

    const ERROR = 'No task available';
    const MESSAGE = 'There is no task available';


}
