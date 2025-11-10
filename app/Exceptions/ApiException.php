<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected int $statusCode;
    protected $errors;

    public function __construct(string $message = "", int $statusCode = 400, $errors = null)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
