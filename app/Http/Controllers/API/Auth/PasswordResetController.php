<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password as rule;
class PasswordResetController extends Controller
{
    // public function sendResetLink(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email|exists:users,email',
    //     ]);

    //     // ارسال لینک بازنشانی رمز عبور
    //     $status = Password::sendResetLink(
    //         $request->only('email')
    //     );

    //     if ($status === Password::RESET_LINK_SENT) {
    //         return response()->json(['message' => 'لینک تنظیم رمز عبور به ایمیل ارسال شد.'], 200);
    //     }

    //     return response()->json(['message' => 'ارسال لینک ناموفق بود.'], 400);
    // }

    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Request password reset email",
     *     description="Sends a password reset link to the given email address. Rate limiting is applied to prevent spam.",
     *     operationId="forgotPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="لینک بازیابی کلمه عبور به ایمیل شما ارسال شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="ما اخیرا یک لینک تنظیم مجدد کلمه عبور برای شما ارسال کرده ایم. لطفا قبل از درخواست مجدد صبر کنید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email|exists:users,email']);

            // set ratelimitter
            $email = $request->email;
            $key = "reset-password-throttle:{$email}";

            if (RateLimiter::tooManyAttempts($key, 1)) {
                return response()->json([
                    'status' => false,
                    'message' => 'ما اخیراً یک لینک تنظیم مجدد کلمه عبور برای شما ارسال کرده‌ایم. لطفاً قبل از درخواست مجدد صبر کنید'
                ], 429);
            }
            RateLimiter::hit($key, 60 * 30);


            $user = User::where('email', $request->email)->firstOrFail();
            $token = Password::createToken($user);

            // send notification
            try {
                $user->notify(new ResetPasswordNotification($token, ['message' => 'لینک بازیابی کلمه عبور بنابر درخواست کاربر به آدرس  ' . $user->email . ' با موفقیت ارسال شد']));
            } catch (Exception $e) {
                Log::error("خطا در ارسال ایمیل تغییر کلمه عبور : " . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
                ], 500);
            }
            return response()->json([
                'status' => true,
                'message' => 'لینک بازیابی کلمه عبور به ایمیل شما ارسال شد'
            ], 200);
        } catch (Exception $e) {
            Log::error('متد فراموشی کلمه عبور با خطا مواجه شد' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset user password",
     *     description="Resets the password using the provided token and new password.",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "token", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="a1b2c3d4e5f6g7h8"),
     *             @OA\Property(property="password", type="string", format="password", example="Ex@mpl8N0risk7"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="Ex@mpl8N0risk7")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="رمز عبور شما با موفقیت تغییر یافت")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="اطلاعات وارد شده نامعتبر است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'status' => true,
                    'message' => 'رمز عبور شما با موفقیت تغییر یافت'
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'اطلاعات واردشده نامعتبر است'
            ], 400);
        } catch (Exception $e) {
            Log::error('Reset password method failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }
}
