<?php

namespace App\Http\Controllers\API\Customer\SalesProcess;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UserProfileRequest;
use App\Http\Services\Message\Email\EmailService;
use App\Http\Services\Message\MessageService;
use App\Http\Services\Message\SMS\SmsService;
use App\Models\Market\CartItem;
use App\Models\Otp;
use App\Traits\UserCartTrait;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
class ProfileCompletionController extends Controller
{
    use UserCartTrait;
    
    /**
     * @OA\Get(
     *     path="/api/profile-required-fields",
     *     summary="Get Required Profile Fields",
     *     description="This endpoint is designed to `help the frontend determine which profile fields should be mandatory when updating the user's profile`. It checks the user's existing data and returns the fields that are currently empty and need to be filled.",
     *     operationId="getRequiredFields",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="List of required fields",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="required_fields",
     *                 type="object",
     *                 example={
     *                      "first_name" : false,
     *                      "last_name" : true ,
     *                      "email" : false,
     *                      "mobile" : true ,
     *                      "national_code" : false,
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getProfileRequiredField(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'data' => [
                'isRequiredField' => [
                    array_filter([
                        'first_name' => empty($user->first_name) ? true : false,
                        'last_name' => empty($user->last_name) ? true : false,
                        'email' => empty($user->email) ? true : false,
                        'mobile' => empty($user->mobile) ? true : false,
                        'national_code' => empty($user->national_code) ? true : false,
                    ])
                ]
            ],
            'meta' => [
                'next_step' => 'redirect_to_/profile-completion'
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/profile-completion", 
     *     summary="Update User Profile",
     *     description="Update Authenticated User Profile Information", 
     *     tags={"Profile"}, 
     *     security={{"bearerAuth":{}}}, 
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="if this field is empty in database,this field will be required"), 
     *             @OA\Property(property="last_name", type="string", example="if this field is empty in database,this field will be required"), 
     *             @OA\Property(property="national_code", type="string", example="if this field is empty in database,this field will be required"), 
     *             @OA\Property(property="mobile", type="string", example="if this field is empty in database,this field will be required"), 
     *             @OA\Property(property="email", type="string", example="if this field is empty in database,this field will be required") 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=200, 
     *         description="Update Profile is done.", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example="true"), 
     *             @OA\Property(property="data", type="string", example="kjdfbmvjdkfjgkjnkhcdj65231bfcjn..."), 
     *             @OA\Property(property="message", type="string", example="اطلاعات حساب کاربری با موفقیت تکمیل شد. اکنون می توانید سفارش خود را نهایی کنید"), 
     *             @OA\Property(property="meta", type="object", 
     *             @OA\Property(property="next_step", type="string", example="redirect_to_/confirm-profile-info/{token}")
     *             ) 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=422, 
     *         description="phone number is not valid", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example="false"), 
     *             @OA\Property(property="message", type="string", example="فرمت شماره موبایل معتبر نیست") 
     *         ) 
     *     ) 
     * ) 
     */
    public function updateProfile(UserProfileRequest $request)
    {
        try {
            $national_code = convertArabicToEnglish($request->national_code);
            $national_code = convertPersianToEnglish($national_code);
            $user = $this->getAuthUser();
            $inputs = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'national_code' => $request->national_code,

            ];
            if (!isset($request->mobile) && !isset($request->email)) {
                $message = 'اطلاعات حساب کاربری با موفقیت تکمیل شد. اکنون می توانید سفارش خود را نهایی کنید';
            }
            if (isset($request->mobile) && empty($user->mobile)) {
                $mobile = convertArabicToEnglish($request->mobile);
                $mobile = convertPersianToEnglish($mobile);

                if (preg_match('/^(\+98|98|0)9\d{9}$/', $mobile)) {
                    $type = 0; //0 => mobile
                    $mobile = ltrim($mobile, '0');
                    $mobile = substr($mobile, 0, 2) === '98' ? substr($mobile, 2) : $mobile;
                    $mobile = str_replace('+98', '', $mobile);
                    $login_id = $mobile;
                    $inputs['mobile'] = $mobile;
                } else {
                    $error_text = 'فرمت شماره موبایل معتبر نیست';
                    return response()->json([
                        'status' => false,
                        'message' => $error_text
                    ], 422);
                }
                if (isset($request->email) && empty($user->email)) {
                    $type = 1; //0 => email
                    $email = convertArabicToEnglish($request->email);
                    $email = convertPersianToEnglish($email);
                    $login_id = $email;
                    $inputs['email'] = $email;
                }
                $inputs = array_filter($inputs);

                if (!empty($inputs)) {
                    $user->update($inputs);
                }

                // if otp was sent; don't resend it until expired
                if ($type == 0) {
                    $oldOtp = Otp::where('login_id', $inputs['mobile'])->where('used', 0)->orderBy('created_at', 'desc')->first();
                } else {
                    $oldOtp = Otp::where('login_id', $inputs['email'])->where('used', 0)->orderBy('created_at', 'desc')->first();

                }
                if ($oldOtp) {
                    $minutes_to_add = 2;
                    $expired = new DateTime($oldOtp->created_at);
                    $expired->add(new DateInterval('PT' . $minutes_to_add . 'M'));
                    $now = new DateTime();
                    $timer = ((new \Carbon\Carbon($oldOtp->created_at))->addMinutes(2)->timestamp - \Carbon\Carbon::now()->timestamp);

                    if ($now < $expired) {
                        return response()->json([
                            'status' => false,
                            'message' => 'جهت ارسال مجدد کد تأیید ' . $timer . ' ثانیه دیگر منتظر بمانید',
                            'data' => [
                                'remainTime' => $timer,
                                'token' => $oldOtp->token
                            ],
                            'meta' => [
                                'next_step' => 'redirect_to_/confirm-profile-info/{token}'
                            ]
                        ], 429);
                    }
                }
                // create otp code
                $otpCode = rand(111111, 999999);
                $token = Str::random(60);
                $otpInputs = [
                    'token' => $token,
                    'user_id' => $user->id,
                    'otp_code' => $otpCode,
                    'login_id' => $login_id,
                    'type' => $type
                ];
                Otp::create($otpInputs);

                // send sms or email

                if ($type == 0 && !isset($user->mobile_verified_at)) {
                    // send sms
                    $smsService = new SmsService();
                    $smsService->setFrom(Config::get('sms.otp_from'));
                    $smsService->setTo(['0' . $user->mobile]);
                    $smsService->setText("مجموعه آمازون\n کد تأیید شما : $otpCode");
                    $smsService->setIsFlash(true);

                    $messageService = new MessageService($smsService);
                    $message = 'کد تایید شماره موبایل برای شما ارسال شد. جهت تکمیل سفارش شماره موبایل خود را تأیید نمایید';
                } elseif ($type == 1 && !isset($user->email_verified_at)) {
                    $emailService = new EmailService();
                    $details = [
                        'title' => 'ایمیل فعال سازی حساب کاربری',
                        'body' => "کد فعال سازی حساب کاربری شما : $otpCode"
                    ];
                    $emailService->setDetails($details);
                    $emailService->setFrom('noreply@example.com', 'amazon');
                    $emailService->setSubject('کد احراز هویت');
                    $emailService->setTo($user->email);
                    $messageService = new MessageService($emailService);
                    $message = 'کد تایید ایمیل برای شما ارسال شد. جهت تکمیل سفارش ایمیل خود را تأیید نمایید';
                }
                $messageService->send();

                return response()->json([
                    'status' => true,
                    'data' => $token,
                    'message' => $message,
                    'meta' => [
                        'next_step' => 'redirect_to_/confirm-profile-info/{token}'
                    ]
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'خطایی غیرمنتظره در سرور رخ داده است. لطفا دوباره تلاش کنید'
            ], 500);
        }


    }

    /**
     * @OA\Post( 
     *     path="/api/confirm-profile-info/{token}", 
     *     summary="Confirm Profile Information With OTP code", 
     *     tags={"Profile"}, 
     *     security={{"bearerAuth":{}}}, 
     *     @OA\Parameter( 
     *         name="token", 
     *         in="path", 
     *         required=true, 
     *         @OA\Schema(type="string") 
     *     ), 
     *    @OA\RequestBody( 
     *         required=true, 
     *         @OA\JsonContent( 
     *             required={"otp"}, 
     *             @OA\Property(property="otp", type="string", minLength=6, maxLength=6) 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=200, 
     *         description="Update Profile SuccessFully", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example="true")), 
     *             @OA\Property(property="message", type="string", example="پروفایل با موفقیت بروزرسانی شد") 
     *         ) 
     *     ), 
     *     @OA\Response( 
     *         response=401, 
     *         description="Otp Code or Url Address is invalid", 
     *         @OA\JsonContent( 
     *             @OA\Property(property="status", type="boolean", example="false"), 
     *             @OA\Property(property="message", type="string", example="کد وارد شده صحیح نیست یا آدرس معتبر نیست"), 
     *             @OA\Property(property="meta", type="object", 
     *                 @OA\Property(property="next_step", type="redirect_to_/profile-completion") 
     *             ) 
     *         ) 
     *     ) 
     * ) 
     */
    public function confirmProfileInfo($token, Request $request)
    {
        $request->validate([
            'otp' => 'required|min:6|max:6'
        ]);
        $inputs = $request->all();
        $user = $this->getAuthUser();
        $cartItems = $this->getCartItems();
        $otp = Otp::where('token', $token)->where('used', 0)->where('created_at', '>=', Carbon::now()->subMinute(2)->toDateTimeString())->first();
        if (empty($otp)) {
            return response()->json([
                'status' => false,
                'data' => [
                    'user' => $user,
                    'cartItems' => $cartItems,
                    'token' => $token
                ],
                'message' => 'آدرس وارد شده معتبر نیست',
                'meta' => [
                    'next_step' => 'redirect_to_/profile-completion'
                ]

            ], 401);
        }
        // if otp code missmatch:
        if ($otp->otp_code !== $inputs['otp']) {
            return response()->json([
                'status' => false,
                'data' => [
                    'user' => $user,
                    'cartItems' => $cartItems,
                    'token' => $token
                ],
                'message' => 'کد وارد شده صحیح نیست',
                'meta' => [
                    'next_step' => 'redirect_to_/profile-completion'
                ]

            ], 401);
        }
        // if everything is ok:
        $otp->update(['used' => 1]);
        $confirmedUser = $otp->user()->first();
        if ($otp->type == 0 && empty($confirmedUser->mobile_verified_at)) {
            $confirmedUser->update(['mobile_verified_at' => Carbon::now()]);
        } elseif ($otp->type == 1 && empty($confirmedUser->email_verified_at)) {
            $confirmedUser->update(['email_verified_at' => Carbon::now()]);
        }

        return response()->json([
            'status' => true,
            'message' => 'پروفایل با موفقیت بروزرسانی شد'
        ]);

    }

    
}
