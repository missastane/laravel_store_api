<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendResetPasswordUrl;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class GoogleAuthController extends Controller
{
    /**
     * @OA\Get( 
     *     path="/api/login/google",
     *     summary="Redirect to Google OAuth",
     *     description="Generates a Google OAuth URL for authentication.", 
     *     tags={"Authentication","Google Auth"}, 
     *     @OA\Response( 
     *         response=200, 
     *         description="Google OAuth URL Generated", 
     *         @OA\JsonContent(
     *            @OA\Property(property="url", type="string", example="https://accounts.google.com/o/oauth2/auth?client_id=...")
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=500, 
     *         description="Server Error", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example=false), 
     *             @OA\Property(property="message", type="string", example="خطایی در هدایت به گوگل رخ داد. لطفاً بعداً امتحان کنید.") 
     *         ) 
     *     ) 
     * )
     */

    public function redirectToGoogle()
    {
        try {
            $googleUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
            return response()->json([
                'url' => $googleUrl
            ], 200);

        } catch (Exception $e) {
            Log::error('ریدایرکت به گوگل با خطا مواجه شد: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'خطایی در هدایت به گوگل رخ داد. لطفاً بعداً امتحان کنید.'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/login/google/callback",
     *     summary="Handle Google OAuth Callback",
     *     description="Handles the callback from Google OAuth, registers/logs in the user, and returns an access token.To test this endpoint, first call the `/api/login/google` endpoint to get the Google authentication URL, authenticate with Google, and obtain the authorization code. Then use this endpoint to complete the login process",
     *     tags={"Authentication","Google Auth"},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         required=true,
     *         description="OAuth authorization code from Google",
     *         @OA\Schema(type="string", example="4/0Ad...QdA")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User Logged in Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1..."),
     *             @OA\Property(property="message", type="string", example="ورود موفقیت‌آمیز بود")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="عملیات با خطا مواجه شد. لطفا مجددا تلاش کنید")
     *         )
     *     )
     * )
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            DB::beginTransaction();
            // get information from google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // if user with this email exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // if user exists but have not google_id set it
                if (!$user->google_id) {
                    $user->google_id = $googleUser->getId();
                    $user->email_verified_at = now();
                    $user->save();
                }
            } else {
                // if user does not exists create one with random password
                $password = Str::random(24);
                $user = User::create([
                    'email' => $googleUser->getEmail(),
                    'email_verified_at' => now(),
                    'first_name' => $googleUser->getName(),
                    'password' => Hash::make($password),
                    'google_id' => $googleUser->getId(),
                ]);

                // send link to reset password for this user
                $passToken = Password::createToken($user);
                SendResetPasswordUrl::dispatch($user, $passToken);
            }

            // login user
            auth()->login($user);

            // create authorization token for user
            $token = $user->createToken('google-login-token')->plainTextToken;
            DB::commit();
            return response()->json([
                'status' => true,
                'token' => $token,
                'message' => 'ورود موفقیت‌آمیز بود' . (isset($passToken) ? '، لینک تغییر رمز عبور به ایمیل ارسال شد' : ''),
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'عملیات با خطا مواجه شد. لطفا مجددا تلاش کنید',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
