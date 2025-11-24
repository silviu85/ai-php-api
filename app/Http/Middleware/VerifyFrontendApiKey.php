<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFrontendApiKey
{
    public function handle(Request $request, Closure $next): Response
    {

        // We expect the key to be in a custom header, e.g., 'X-Client-Key'
        $clientKey = $request->header('X-Client-Key');
        $serverKey = env('FRONTEND_API_KEY');

        // Check if the server key is set and if the client sent the correct one.
        if (!$serverKey || $clientKey !== $serverKey) {
            // If the key is missing or incorrect, deny access.
            return response()->json(['error' => 'Forbidden: Invalid client key.'], 403);
        }

        return $next($request);
    }
}