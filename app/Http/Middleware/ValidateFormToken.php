<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ValidateFormToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Form-Token');

        if (!$token || !Cache::has('form_token_' . $token)) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        Cache::forget('form_token_' . $token);

        return $next($request);
    }
}
