<?php

namespace Tests\Concerns;

use GoCardlessPro\Core\ApiResponse;
use GuzzleHttp\Psr7\Response;

/**
 * Builds GoCardless API exceptions the way the SDK itself builds them — from a
 * decoded API error envelope.
 *
 * The SDK's ApiException constructor reads `$api_response->body->error`, so
 * handing it a bare string (as these tests used to) leaves `api_error` null and
 * only appeared to work because the app suppressed notices.
 */
trait MakesGoCardlessExceptions
{
    /**
     * @param string $class   a GoCardlessPro\Core\Exception\* class name
     * @param string $message the error message the API would return
     * @param string $type    GoCardless error type, e.g. validation_failed
     * @param int    $code    HTTP status the API would return
     */
    protected function gocardlessException($class, $message, $type = 'validation_failed', $code = 422)
    {
        $body = [
            'error' => [
                'message' => $message,
                'code' => $code,
                'type' => $type,
                'documentation_url' => 'https://developer.gocardless.com/api-reference#' . $type,
                'request_id' => 'test_request_id',
                // Mirrors the top-level message so getErrorMessage() returns it unchanged.
                'errors' => [['message' => $message, 'reason' => $type]],
            ],
        ];

        return new $class(new ApiResponse(new Response($code, [], json_encode($body))));
    }

    protected function gocardlessValidationFailed($message)
    {
        return $this->gocardlessException(
            \GoCardlessPro\Core\Exception\ValidationFailedException::class,
            $message,
            'validation_failed',
            422
        );
    }

    protected function gocardlessInvalidState($message)
    {
        return $this->gocardlessException(
            \GoCardlessPro\Core\Exception\InvalidStateException::class,
            $message,
            'invalid_state',
            422
        );
    }
}
