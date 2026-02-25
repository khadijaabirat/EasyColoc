<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next) 
    {
      if (Auth::check() && Auth::user()->is_banned) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account has been banned.');
        }
        return $next($request);
    }
}
