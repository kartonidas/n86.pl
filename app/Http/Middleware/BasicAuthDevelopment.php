<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthDevelopment
{
    public function handle(Request $request, Closure $next): Response
    {
        if(env("APP_DEV_MODE", false))
        {
            if($request->is("api/*"))
                return $next($request);
            
            $user = $_SERVER["PHP_AUTH_USER"] ?? "";
            $pass = $_SERVER["PHP_AUTH_PW"] ?? "";

            $validated = $user == env("APP_DEV_USER") && $pass == env("APP_DEV_PASS");
            
            if (!$validated) {
                header("WWW-Authenticate: Basic realm=\"My Realm\"");
                header("HTTP/1.0 401 Unauthorized");
                exit;
            } 
        }
        return $next($request);
    }
}
