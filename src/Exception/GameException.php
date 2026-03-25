<?php

namespace App\Exception;

use Exception;
use Throwable;

class GameException extends Exception{
    public function __construct(string $message = "Erreur concernant la création des match", int $code = 400, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}