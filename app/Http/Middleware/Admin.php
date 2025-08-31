<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'قبل از انجام عملیات ابتدا وارد حساب کاربری خود شوید23333'
            ], 401);
        }
        if (Auth::user()->user_type != 1) {
            return response()->json([
                'status' => false,
                'message' => 'شما مجوز دسترسی به این بخش را ندارید'
            ], 403);
        }


        return $next($request);
    }
}
