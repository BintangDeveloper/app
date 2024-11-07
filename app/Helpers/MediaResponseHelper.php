<?php

namespace App\Helpers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use finfo;

class MediaResponseHelper
{
    /**
     * Return a media response from a file path, string, or binary buffer, with auto MIME type detection.
     *
     * @param mixed $content The media content, which can be a file path, a string, or a binary buffer.
     * @param string|null $mimeType Optional MIME type. If null, it will be auto-detected.
     * @param int $code The HTTP status code.
     * @param array $headers Optional additional HTTP headers.
     * @return StreamedResponse|Response
     */
    public static function media(
        mixed $content,
        string $mimeType = null,
        int $code = 200,
        array $headers = []
    ): StreamedResponse|Response {
        // If MIME type is not provided, auto-detect it
        if ($mimeType === null) {
            $mimeType = self::detectMimeType($content);
            if ($mimeType === null) {
                return self::error("Could not determine MIME type", 415, $headers);
            }
        }

        // Determine if content is a file path, a string, or binary data
        if (is_string($content) && file_exists($content)) {
            return self::streamFile($content, $mimeType, $code, $headers);
        } elseif (is_string($content) || is_resource($content)) {
            return self::streamRawContent($content, $mimeType, $code, $headers);
        }

        // Invalid content type
        return self::error("Invalid media content provided", 400, $headers);
    }
    
    /**
     * Force download a file or binary content with auto MIME type detection.
     *
     * @param mixed $content The media content, which can be a file path, a string, or a binary buffer.
     * @param string|null $fileName Optional file name for the download.
     * @param string|null $mimeType Optional MIME type. If null, it will be auto-detected.
     * @param array $headers Optional additional HTTP headers.
     * @return StreamedResponse|Response
     */
    public static function download(
        mixed $content,
        string $fileName = null,
        string $mimeType = null,
        array $headers = []
    ): StreamedResponse|Response {
        // If MIME type is not provided, auto-detect it
        if ($mimeType === null) {
            $mimeType = self::detectMimeType($content);
            if ($mimeType === null) {
                return self::error("Could not determine MIME type", 415, $headers);
            }
        }
        
        if ($fileName === null) {
          $fileName = "BintangDeveloper-" . hash('sha1', date("Y-m-d H:i:s"));
        }

        // Set headers for download
        $headers = array_merge($headers, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'public, max-age=3600',
        ]);

        // If content is a file path, stream the file; otherwise, stream the raw content
        if (is_string($content) && file_exists($content)) {
            return self::streamFile($content, $mimeType, 200, $headers);
        } elseif (is_string($content) || is_resource($content)) {
            return self::streamRawContent($content, $mimeType, 200, $headers);
        }

        // Invalid content type
        return self::error("Invalid media content provided", 400, $headers);
    }

    /**
     * Detect MIME type from content.
     *
     * @param mixed $content The media content as a file path, string, or binary buffer.
     * @return string|null The detected MIME type or null if undetectable.
     */
    private static function detectMimeType(mixed $content): ?string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        if (is_string($content) && file_exists($content)) {
            // Detect MIME type for file path
            return $finfo->file($content);
        } elseif (is_string($content)) {
            // Detect MIME type for binary data in a string
            return $finfo->buffer($content);
        }

        return null;
    }

    /**
     * Stream a file from a file path.
     *
     * @param string $filePath The path to the media file.
     * @param string $mimeType The MIME type of the media file.
     * @param int $code The HTTP status code.
     * @param array $headers Optional HTTP headers.
     * @return StreamedResponse
     */
    private static function streamFile(
        string $filePath,
        string $mimeType,
        int $code,
        array $headers
    ): StreamedResponse {
        $headers = array_merge($headers, [
            'Content-Type' => $mimeType,
            'Content-Length' => filesize($filePath),
            'Cache-Control' => 'public, max-age=3600',
        ]);

        return response()->stream(function () use ($filePath) {
            readfile($filePath);
        }, $code, $headers);
    }

    /**
     * Stream raw content, either from a string or binary data.
     *
     * @param string|resource $content The media content as a string or binary buffer.
     * @param string $mimeType The MIME type of the media.
     * @param int $code The HTTP status code.
     * @param array $headers Optional HTTP headers.
     * @return StreamedResponse
     */
    private static function streamRawContent(
        string|resource $content,
        string $mimeType,
        int $code,
        array $headers
    ): StreamedResponse {
        $headers = array_merge($headers, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
        ]);

        return response()->stream(function () use ($content) {
            if (is_resource($content)) {
                fpassthru($content);
            } else {
                echo $content;
            }
        }, $code, $headers);
    }

    /**
     * Return an error response for missing or unavailable media.
     *
     * @param string $message The error message.
     * @param int $code The HTTP status code.
     * @param array $headers Optional HTTP headers.
     * @return Response
     */
    public static function error(
        string $message,
        int $code = 404,
        array $headers = []
    ): Response {
        $headers = array_merge($headers, [
            'Content-Type' => 'application/json; charset=utf-8'
        ]);

        return ResponseHelper::error($message, [], 500, $headers);
    }
}
