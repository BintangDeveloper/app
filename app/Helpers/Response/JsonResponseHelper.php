<?php

namespace App\Helpers\Response;

use Illuminate\Http\JsonResponse;

class JsonResponseHelper
{
    /**
     * Enable JSON pretty print.
     *
     * @var bool
     */
    private static bool $prettyPrint = true;

    /**
     * Constructor to set pretty print.
     *
     * @param bool $prettyPrint
     */
    public function __construct(bool $prettyPrint = true)
    {
        self::$prettyPrint = $prettyPrint;
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
        $responseTime = round((microtime(true) - LARAVEL_START) * 1000);

        $response = [
            'status' => $isError ? 'error' : 'success',
            'responseTime' => "{$responseTime}ms",
            $isError ? 'error' : 'data' => $data,
            'timestamp' => date("Y-m-d H:i:s"),
        ];

        if (!empty($meta)) {
            if ($isError) {
                $response['error']['details'] = $meta;
            } else {
                $response['meta'] = $meta;
            }
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
        return self::jsonResponse($data, $meta, $code, $headers);
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
        $data = ['code' => $code, 'message' => $message];
        return self::jsonResponse($data, $details, $code, $headers, true);
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
        return self::success($data, $code, $meta, $headers);
    }

    /**
     * Generate a JSON response.
     *
     * @param mixed $data
     * @param array $meta
     * @param int $code
     * @param array $headers
     * @param bool $isError
     * @return JsonResponse
     */
    private static function jsonResponse(
        mixed $data, 
        array $meta, 
        int $code, 
        array $headers, 
        bool $isError = false
    ): jsonresponse {
        $response = self::buildResponse($data, $meta, $isError);
        $jsonOptions = (self::$prettyPrint ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE;

        return response()->json($response, $code, [
          'Content-Type'=>'application/json; charset=utf-8'
        ], $jsonOptions)->withHeaders($headers);
    }
}
