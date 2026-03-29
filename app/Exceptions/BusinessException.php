<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected int $statusCode;
    protected array $errors;

    public function __construct(
        string $message = "Business Logic Error",
        int $statusCode = 422,
        array $errors = []
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errors
        ], $this->statusCode);
    }
}
