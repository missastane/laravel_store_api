<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="User Registration",
     *     description="`Registers` a new user and send an email to verified entered email",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="missastaneh@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", description="password must be include letters,numbers and symbols and be uncompromised", example="Ex@mpl8N0risk7"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", description="This field must be same as password field exactly", example="Ex@mpl8N0risk7")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful Registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="با تشکر از ثبت نام شما. لینک تأیید ایمیل به ادرس ایمیل وارد شده ارسال گردید. لطفا ابتدا ایمیل خود را تأیید فرمایید"),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1...")
     *         )
     *     ),
     *      @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفاً مجددا تلاش کنید.")
     *     )
     *   )
     * )
     */
    public function register(LoginRegisterRequest $request)
    {
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->sendEmailVerificationNotification();
            return response()->json([
                'status' => true,
                'message' => 'با تشکر از ثبت نام شما. لینک تأیید ایمیل به آدرس ایمیل وارد شده ارسال گردید. لطفا ابتدا ایمیل خود را تأیید فرمایید',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا مجددا تلاش کنید',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     description="Authenticates a user and returns an `access token`. also this method `deletes old user tokens for more saftfy`.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="missastaneh@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Ex@mpl8N0risk7")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful Login",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="کاربری با این مشخصات یافت نشد")
     *         )
     *     ),
     *  @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="ابتدا ایمیل خود را تأیید کنید")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفاً مجددا تلاش کنید.")
     *     )
     *   )
     * )
     */
    public function login(LoginRegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'کاربری با این مشخصات یافت نشد'
                ], 401);
            }
            if(!$user->hasVerifiedEmail()){
                return response()->json([
                    'status' => false,
                    'message' => 'ابتدا ایمیل خود را تأیید کنید'
                ], 403);
            }
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            DB::commit();
            return response()->json([
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا مجددا تلاش کنید'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/logout",
     *     summary="User Logout",
     *     description="Logs out the authenticated user by deleting their token.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful Logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="کاربر از حساب کاربری خارج شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="کاربری با این مشخصات یافت نشد")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'کاربری با این مشخصات یافت نشد'
            ], 401);
        }
        $user->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'کاربر از حساب کاربری خارج شد'
        ], 200);
    }
}
