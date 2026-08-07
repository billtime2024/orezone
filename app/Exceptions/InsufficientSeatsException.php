<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientSeatsException extends RuntimeException
{
    public function __construct(string $message = 'Insufficient seats available.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
