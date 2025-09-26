<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DevelopmentCsp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $viteDev = file_exists(public_path('hot'));

        $scriptSrc = ["'self'", "'unsafe-eval'", "'unsafe-inline'"];
        $styleSrc = ["'self'", "'unsafe-inline'", 'https://fonts.bunny.net'];
        $connectSrc = [
            "'self'",
            'ws://127.0.0.1:8080',
            'wss://127.0.0.1:8080',
        ];

        if ($viteDev) {
            $scriptSrc[] = 'http://127.0.0.1:5173';
            $styleSrc[] = 'http://127.0.0.1:5173';
            $connectSrc[] = 'http://127.0.0.1:5173';
            $connectSrc[] = 'ws://127.0.0.1:5173';
        }

        $csp = "default-src 'self'; "
             .'script-src '.implode(' ', $scriptSrc).'; '
             .'style-src '.implode(' ', $styleSrc).'; '
             ."font-src 'self' https://fonts.bunny.net; "
             .'connect-src '.implode(' ', $connectSrc).';';

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
