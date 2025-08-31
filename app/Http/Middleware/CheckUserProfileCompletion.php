<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // چک می‌کنیم که اطلاعات ضروری وارد شده باشند
        if (!$user->first_name || !$user->last_name || !$user->national_code || !$user->mobile_verified_at) {
            return response()->json([
                'message' => 'لطفاً اطلاعات حساب خود را تکمیل کنید.',
                'missing_fields' => [
                    'first_name' => !$user->first_name ? 'نام وارد نشده است.' : null,
                    'last_name' => !$user->last_name ? 'نام خانوادگی وارد نشده است.' : null,
                    'national_code' => !$user->national_code ? 'کد ملی وارد نشده است.' : null,
                    'mobile_verified' => !$user->mobile_verified_at ? 'موبایل تایید نشده است.' : null,
                ]
            ], 400); // کد 400 یعنی درخواست ناقصه
        }
        return $next($request);
    }
}
