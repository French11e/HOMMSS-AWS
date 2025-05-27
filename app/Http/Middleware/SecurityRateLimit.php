<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SecurityRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'api'): Response
    {
        $limits = [
            'login' => env('RATE_LIMIT_LOGIN', 5),
            'api' => env('RATE_LIMIT_API', 60),
            'search' => env('RATE_LIMIT_SEARCH', 30),
        ];

        $limit = $limits[$type] ?? 60;
        $key = $this->resolveRequestSignature($request, $type);

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $seconds = RateLimiter::availableIn($key);
            
            // Log rate limit violation
            \Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'type' => $type,
                'limit' => $limit,
                'retry_after' => $seconds
            ]);

            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 60); // 1 minute window

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $limit),
        ]);
    }

    /**
     * Resolve the rate limiting signature for the request.
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        $userId = $request->user()?->id ?? 'guest';
        return sha1($type . '|' . $request->ip() . '|' . $userId);
    }
}
