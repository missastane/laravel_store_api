<?php

namespace App\Http\Controllers\API\Customer\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profile\UpdateProfileRequest;
use App\Http\Services\Message\Email\EmailService;
use App\Http\Services\Message\MessageService;
use App\Http\Services\Message\SMS\SmsService;
use App\Http\Services\OTP\OTPService;
use App\Models\Otp;
use App\Models\User;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get User Profile Information",
     *     description="This Endpoint returns Authenticated User Information to make a profile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfull",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();
        return response()->json([
            'data' => $user
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/profile/update",
     *     summary="Update User's Personal Information",
     *     description="This method updates authenticated user's personal information",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "national_code"},
     *             @OA\Property(property="first_name", type="string", example="علی"),
     *             @OA\Property(property="last_name", type="string", example="رضایی"),
     *             @OA\Property(property="national_code", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile Has Updated Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="حساب کاربری شما با موفقیت ویرایش شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function update(UpdateProfileRequest $request)
    {
        $inputs = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'national_code' => $request->national_code,
        ];
        try {
            $user = auth()->user();
            $user->update($inputs);
            return response()->json([
                'status' => true,
                'message' => 'حساب کاربری شما با موفقیت ویرایش شد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }


    /**
     * @OA\Put( 
     *     path="/api/profile/edit-contact", 
     *     summary="Send Otp to Update Mobile Or Email", 
     *     description="This method sends Otp code to change and confirm mobile or email", 
     *     tags={"Profile"}, 
     *     security={{"bearerAuth":{}}}, 
     *     @OA\RequestBody( 
     *         required=true, 
     *         @OA\JsonContent( 
     *             required={"id"}, 
     *             @OA\Property(property="id", type="string", example="example@example.com") 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=200, 
     *         description="OTP Send Successfully", 
     *         @OA\JsonContent( 
     *             type="object", 
     *             @OA\Property(property="status", type="boolean", example=true), 
     *             @OA\Property(property="message", type="string", example="جهت ویرایش موبایل یا ایمیل خود، لطفا کد تأیید ارسال‌شده را وارد کنید"), 
     *             @OA\Property(property="data", type="string", example="token_value") 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=422, 
     *         description="Invalid Input", 
     *         @OA\JsonContent( 
     *             type="object", 
     *             @OA\Property(property="status", type="boolean", example=false), 
     *             @OA\Property(property="message", type="string", example="ایمیل یا شماره موبایل باید منحصربفرد باشد") 
     *         ) 
     *     ) 
     * ) 
     */
    public function mobileOrEmailEdit(Request $request, OTPService $oTPService)
    {
        $request->validate([
            'id' => 'required|min:11|max:64|regex:/^[a-zA-Z0-9_.@\+]*$/'
        ]);
        try {
            $inputs = $request->all();
            $oldUser = User::where('email', $inputs['id'])->orWhere('mobile', ltrim(preg_replace('/^(\+98|0)/','',$inputs['id'])))->first();
            if (!empty($oldUser)) {
                return response()->json([
                    'status' => false,
                    'message' => 'ایمیل یا شماره موبایل باید منحصربفرد باشد'
                ], 422);
            }
            if (filter_var($inputs['id'], FILTER_VALIDATE_EMAIL)) {
                $type = 1;  //id is an email
            } elseif (preg_match('/^(\+98|98|0)9\d{9}$/', $inputs['id'])) {
                $type = 0; //id is a mobile number;

                // all mobile number are in one format 9** *** ****
                $inputs['id'] = ltrim($inputs['id'], '0');
                $inputs['id'] = substr($inputs['id'], 0, 2) === '98' ? substr($inputs['id'], 2) : $inputs['id'];
                $inputs['id'] = str_replace('+98', '', $inputs['id']);
            } else {
                return response()->json([
                    'status' => false,
                    'meaasge' => 'شناسه ورودی شما ایمیل یا شماره موبایل نیست'
                ], 422);
            }
            $user = auth()->user();
            $otp = $oTPService->createOtp($inputs['id'], $type, $user->id);
            if ($type == 0) {
                $oTPService->sendSms($inputs['id'], $otp->otp_code);
            } elseif ($type == 1) {
                $oTPService->sendEmail($inputs['id'], $otp->otp_code);
            }
            return response()->json([
                'status' => true,
                'message' => 'جهت ویرایش موبایل یا ایمیل خود با وارد کردن کد تأیید 6 رقمی ارسال شده لطفا آن را تأیید نمایید',
                'data' => $otp->token,
                'meta' => [
                    'next_step' => 'redirect_to_/confirm_otp_page'
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/profile/confirm-contact/{token}",
     *     summary="Confirm OTP to change Mobile or Email",
     *     description="This method Update Mobile or Email if OTP code is valid",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="token from otp record",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"otp"},
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email or Mobile is Updated Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="اطلاعات حساب کاربری شما با موفقیت تغییر کرد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="OTP is Invalid",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="کد وارد شده معتبر نیست")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal servr error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function userCantactConfirm($token, Request $request)
    {
        $request->validate([
            'otp' => 'required|min:6|max:6'
        ]);
        try {
            $inputs = $request->all();
            $user = auth()->user();
            $otp = Otp::where('token', $token)->where('user_id', $user->id)->where('used', 0)->where('created_at', '>=', Carbon::now()->subMinute(2)->toDateTimeString())->first();
            if (empty($otp)) {
                return response()->json([
                    'status' => false,
                    'data' => [
                        'token' => $token
                    ],
                    'message' => 'آدرس وارد شده معتبر نیست',
                    'meta' => [
                        'next_step' => 'redirect_back'
                    ]

                ], 401);
            }
            // if otp code missmatch:
            if ($otp->otp_code !== $inputs['otp']) {
                return response()->json([
                    'status' => false,
                    'data' => [
                        'token' => $token
                    ],
                    'message' => 'کد وارد شده معتبر نیست',
                    'meta' => [
                        'next_step' => 'redirect_back'
                    ]

                ], 401);
            }
            // if everything is ok:
            $otp->update(['used' => 1]);
            if ($otp->type == 0) {
                $user->update(['mobile_verified_at' => Carbon::now(), 'mobile' => $otp->login_id]);
            } elseif ($otp->type == 1) {
                $user->update(['email_verified_at' => Carbon::now(), 'email' => $otp->login_id]);
            }
            return response()->json([
                'status' => true,
                'message' => 'اطلاعات حساب کاربری شما با موفقیت تغییر کرد'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }
    }


}
