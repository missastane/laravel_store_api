<?php

namespace App\Http\Controllers\API\Auth\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Customer\LoginRegisterRequest;
use App\Http\Services\Message\Email\EmailService;
use App\Http\Services\Message\MessageService;
use App\Http\Services\Message\SMS\SmsService;
use App\Models\Otp;
use App\Models\User;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
class LoginRegisterController extends Controller
{
    public function loginRegisterForm()
    {
        return view('customer.auth.login-register');
    }


    public function loginRegister(LoginRegisterRequest $request)
    {
        $inputs = $request->all();
        // check id is email or not;
        if (filter_var($inputs['id'], FILTER_VALIDATE_EMAIL)) {
            $type = 1;  //id is an email
            $user = User::where('email', $inputs['id'])->first();
            if (empty($user)) {
                $newUser['email'] = $inputs['id'];
            }
        }

        // check id is mobile or not;
        elseif (preg_match('/^(\+98|98|0)9\d{9}$/', $inputs['id'])) {
            $type = 0; //id is a mobile number;

            // all mobile number are in one format 9** *** ****
            $inputs['id'] = ltrim($inputs['id'], '0');
            $inputs['id'] = substr($inputs['id'], 0, 2) === '98' ? substr($inputs['id'], 2) : $inputs['id'];
            $inputs['id'] = str_replace('+98', '', $inputs['id']);

            $user = User::where('mobile', $inputs['id'])->first();
            if (empty($user)) {
                $newUser['mobile'] = $inputs['id'];
            }
        } else {
            $errorText = 'شناسه ورودی شما ایمیل یا شماره موبایل نیست';
            return redirect()->route('auth.customer.login-register-form')->withErrors(['id' => $errorText]);
        }

        //  user registering

        if (empty($user)) {
            $newUser['password'] = '98355154';
            $newUser['activation'] = 1;
            $user = User::create($newUser);
        }
        // if otp was sent; don't resend it until expired
        $oldOtp = Otp::where('login_id', $inputs['id'])->where('used', 0)->orderBy('created_at', 'desc')->first();
        if ($oldOtp) {
            $minutes_to_add = 2;
            $expired = new DateTime($oldOtp->created_at);
            $expired->add(new DateInterval('PT' . $minutes_to_add . 'M'));
            $now = new DateTime();
            $timer = ((new \Carbon\Carbon($oldOtp->created_at))->addMinutes(2)->timestamp - \Carbon\Carbon::now()->timestamp) * 1000;

            if ($now < $expired) {
                return redirect()->route('auth.customer.login-confirm-form', $oldOtp->token)->with('timer', $timer);
            }
        }
        // create otp code
        $otpCode = rand(111111, 999999);
        $token = Str::random(60);

        $otpInputs = [
            'token' => $token,
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'login_id' => $inputs['id'],
            'type' => $type
        ];

        $newOtp = Otp::create($otpInputs);

        // send sms or email

        if ($type == 0) {
            // send sms
            $smsService = new SmsService();
            $smsService->setFrom(Config::get('sms.otp_from'));
            $smsService->setTo(['0' . $user->mobile]);
            $smsService->setText("مجموعه آمازون\n کد تأیید شما : $otpCode");
            $smsService->setIsFlash(true);

            $messageService = new MessageService($smsService);

        } elseif ($type == 1) {
            $emailService = new EmailService();
            $details = [
                'title' => 'ایمیل فعال سازی حساب کاربری',
                'body' => "کد فعال سازی حساب کاربری شما : $otpCode"
            ];
            $emailService->setDetails($details);
            $emailService->setFrom('noreply@example.com', 'amazon');
            $emailService->setSubject('کد احراز هویت');
            $emailService->setTo($inputs['id']);
            $messageService = new MessageService($emailService);
        }
        $messageService->send();
        return redirect()->route('auth.customer.login-confirm-form', $token);
    }

    public function loginConfirmForm($token)
    {

        $otp = Otp::where('token', $token)->first();
        if (empty($otp)) {
            return redirect()->route('auth.customer.login-register-form')->withErrors(['id' => 'آدرس وارد شده معتبر نیست']);
        }
        return view('customer.auth.login-confirm', compact('token', 'otp'));
    }

    public function loginConfirm($token, LoginRegisterRequest $request)
    {
        $inputs = $request->all();
        $otp = Otp::where('token', $token)->where('used', 0)->where('created_at', '>=', Carbon::now()->subMinutes(2)->toDateTimeString())->first();
        if (empty($otp)) {
            return redirect()->route('auth.customer.login-register-form', $token)->withErrors(['id' => 'آدرس وارد شده معتبر نیست']);
        }
        // if otp code missmatch:
        if ($otp->otp_code !== $inputs['otp']) {
            return redirect()->route('auth.customer.login-register-form', $token)->withErrors(['otp' => 'کد وارد شده صحیح نیست']);
        }
        // if everything is ok:
        $otp->update(['used' => 1]);
        $user = $otp->user()->first();
        if ($otp->type == 0 && empty($user->mobile_verified_at)) {
            $user->update(['mobile_verified_at' => Carbon::now()]);
        } elseif ($otp->type == 1 && empty($user->mobile_verified_at)) {
            $user->update(['email_verified_at' => Carbon::now()]);
        }
        Auth::login($user);
        return redirect()->route('customer.home');
    }

    public function loginResendOtp($token)
    {
        $otp = Otp::where('token', $token)->where('created_at', '<=', Carbon::now()->subMinutes(2)->toDateTimeString())->first();
        if (empty($otp)) {
            return redirect()->route('auth.customer.login-register-form', $token)->withErrors(['id' => 'آدرس وارد شده معتبر نیست']);
        }
        $user = $otp->user()->first();
        // create otp code
        $otpCode = rand(111111, 999999);
        $token = Str::random(60);
        $otpInputs = [
            'token' => $token,
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'login_id' => $otp->login_id,
            'type' => $otp->type
        ];
        Otp::create($otpInputs);

        // send sms or email

        if ($otp->type == 0) {
            // send sms
            $smsService = new SmsService();
            $smsService->setFrom(Config::get('sms.otp_from'));
            $smsService->setTo(['0' . $user->mobile]);
            $smsService->setText("مجموعه آمازون\n کد تأیید شما : $otpCode");
            $smsService->setIsFlash(true);

            $messageService = new MessageService($smsService);

        } elseif ($otp->type == 1) {
            $emailService = new EmailService();
            $details = [
                'title' => 'ایمیل فعال سازی حساب کاربری',
                'body' => "کد فعال سازی حساب کاربری شما : $otpCode"
            ];
            $emailService->setDetails($details);
            $emailService->setFrom('noreply@example.com', 'amazon');
            $emailService->setSubject('کد احراز هویت');
            $emailService->setTo($otp->login_id);
            $messageService = new MessageService($emailService);

        }
        $messageService->send();
        return redirect()->route('auth.customer.login-confirm-form', $token);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('customer.home');
    }
}
