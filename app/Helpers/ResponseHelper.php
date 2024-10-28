<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the response structure.
     *
     * @param mixed $data The main content or error of the response.
     * @param array $meta Metadata or error details.
     * @param bool $isError Flag for determining success or error structure.
     * @return array
     */
    private static function buildResponse(
        mixed $data, 
        array $meta = [], 
        bool $isError = false
    ): array {
        $responseTime = round((microtime(true) - LARAVEL_START) * 1000); // Calculate response time in ms
        $response = [
            'responseTime' => "{$responseTime}ms",
            $isError ? 'error' : 'data' => $data,
        ];

        if (!empty($meta)) {
            $response[$isError ? 'error' : 'meta'] = $meta;
        }

        return $response;
    }

    /**
     * Return a success response.
     *
     * @param mixed $data The data to be returned.
     * @param int $code The HTTP status code.
     * @param array $meta Optional metadata to include in the response.
     * @param array $headers Optional HTTP headers.
     * @return JsonResponse
     */
    public static function success(
        mixed $data, 
        int $code = 200, 
        array $meta = [], 
        array $headers = []
    ): JsonResponse {
        $response = self::buildResponse(['body' => $data], $meta);
        return response()->json($response, $code)->withHeaders($headers);
    }

    /**
     * Return an error response.
     *
     * @param string $message The error message.
     * @param array $details Additional error details.
     * @param int $code The HTTP status code.
     * @param array $headers Optional HTTP headers.
     * @return JsonResponse
     */
    public static function error(
        string $message, 
        array $details = [], 
        int $code = 500, 
        array $headers = []
    ): JsonResponse {
        $response = self::buildResponse(
            ['code' => $code, 'message' => $message], 
            $details, 
            isError: true
        );
        return response()->json($response, $code)->withHeaders($headers);
    }

    /**
     * Return a paginated success response.
     *
     * @param mixed $data The paginated data to be returned.
     * @param int $code The HTTP status code.
     * @param array $meta Additional metadata, such as pagination info.
     * @param array $headers Optional HTTP headers.
     * @return JsonResponse
     */
    public static function paginatedSuccess(
        mixed $data, 
        int $code = 200, 
        array $meta = [], 
        array $headers = []
    ): JsonResponse {
        $response = self::buildResponse($data, $meta);
        return response()->json($response, $code)->withHeaders($headers);
    }
}
