<?php

namespace Framework;

use Framework\Exceptions\ModelNotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Response;
use Exception;

use function Framework\response;

class ApiExceptionHandler
{
    public static function get_response(Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            $response = [
                'message' => $exception->getMessage(),
                'errors' => $exception->get_errors(),
            ];

            return response()->json($response, Response::UNPROCESSABLE_ENTITY);
        }

        if ($exception instanceof ModelNotFoundException) {
            $response = [
                'message' => $exception->getMessage(),
            ];

            return response()->json($response, Response::NOT_FOUND);
        }

        return static::fallback_response($exception);
    }

    protected static function fallback_response(Exception $exception)
    {
        $status_code = (int) $exception->getCode();

        if ($status_code < 100 || $status_code > 599) {
            $status_code = Response::INTERNAL_SERVER_ERROR; // fallback to 500
        }

        $response = [
            'message' => $exception->getMessage(),
        ];

        return response()->json($response, $status_code);
    }
}
