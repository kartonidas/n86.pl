<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Exceptions\PackageRequired;
use App\Models\Subscription;

class Package
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subscription = Subscription::where("status", Subscription::STATUS_ACTIVE)->first();
        if(!$subscription)
            throw new PackageRequired(__("Active package required"));
        
        return $next($request);
    }
}
