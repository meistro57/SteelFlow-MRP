<?php
// app/Http/Middleware/RequestContextMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RequestContextMiddleware
{
    /**
     * Attach contextual data to every log line and record request lifecycle timing.
     */
    public function handle(Request $request, Closure $next)
    {
        $requestId = Str::uuid()->toString();
        $startTime = microtime(true);

        Log::withContext([
            'request_id' => $requestId,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'route' => optional($request->route())->getName(),
        ]);

        Log::info('Incoming request received', [
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => $request->query(),
        ]);

        try {
            $response = $next($request);
        } catch (\Throwable $exception) {
            Log::error('Request handling failed', [
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'trace_sample' => collect(explode(PHP_EOL, $exception->getTraceAsString()))
                    ->take(5)
                    ->toArray(),
            ]);

            throw $exception;
        }

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('Response dispatched', [
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'response_type' => get_class($response),
        ]);

        return $response;
    }
}
