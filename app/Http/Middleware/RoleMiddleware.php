<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next)
    {
        if($request->user()->role === "user")
            return response()->json(["message" => "Sorry You Cant Do This Action"], 401);
        return $next($request);
    }
}
