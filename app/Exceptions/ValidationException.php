<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class ValidationException extends Exception
{
    protected $errors;

    public function __construct(MessageBag $errors)
    {
        parent::__construct('The given data was invalid.');
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
} 