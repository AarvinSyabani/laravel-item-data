<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add security headers
        $response = $next($request);
        
        // Prevent browsers from MIME-sniffing a response away from the declared content type
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Prevent a page from being framed by another domain
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Block pages from loading if cross-site scripting (XSS) attack is detected
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Instruct browser to strictly use HTTPS for the domain
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        
        // Set content security policy
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'");
        
        // Set referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Prevent API response caching
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }
}
