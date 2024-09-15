<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response->isSuccessful() && $response->headers->get('Content-Type') === 'text/html') {
            // Remove unnecessary spaces, tabs, and newlines
            $minifiedContent = preg_replace(
                [
                  '/>\s+</', 
                  '/\s\s+/'
                ],
                [
                  '><', 
                  ' '
                ],
                $response->getContent()
            );
            $response->setContent($minifiedContent);
        }

        return $response;
    }
}
