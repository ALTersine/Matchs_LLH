<?php

namespace App\Exception;

use Exception;
use Throwable;

class CsvException extends Exception{
    public function __construct(string $message = "Erreur concernant le CSV", int $code = 400, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}