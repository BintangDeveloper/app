<?php

namespace App\Helpers;

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
        
        $response = [
            'data' => [
              'body' => $data,
              'responseTime' => 'inMs'
            ]
        ];

        if (!empty($meta)) {
            $response['body']['meta'] = $meta;
        }

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
        
        $response = [
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details
            ]
        ];

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
        
        $response = [
            'data' => $data,
            'meta' => $meta
        ];

        return response()->json($response, $code)->withHeaders($headers);
    }
}
