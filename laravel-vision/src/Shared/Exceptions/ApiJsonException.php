<?php

namespace Shared\Exceptions;

use Exception;

/**
 * API exception returning the error in JSON format - used for consistent error reporting in HTTP responses.
 */
class ApiJsonException extends Exception
{
    /**
     * Creates the exception with a message and an HTTP code that will be returned in the JSON response.
     *
     * @param string $message Error description shown to the client.
     * @param int $code HTTP response code (e.g. 500, 403, 404).
     */
    public function __construct(string $message = 'Server error', int $code = 500)
    {
        parent::__construct($message, $code);
    }

    /**
     * Transforms the exception into a ready JSON response with an error field and the appropriate HTTP code.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with the error message.
     */
    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => $this->getMessage(),
        ], $this->getCode());
    }
}
