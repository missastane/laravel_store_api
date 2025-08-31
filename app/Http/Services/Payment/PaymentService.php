<?php

namespace App\Http\Services\Payment;

use App\Models\Market\OnlinePayment;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Zarinpal\Zarinpal;
use Zarinpal\Clients\GuzzleClient;

class PaymentService
{

    public function zarinpal($amount, $order, $onlinePayment)
    {
        $merchantId = Config::get('payment.zarinpal_merchant_id');
        // sandbox in realy project must be false
        $sandBox = true;
        $zarinpalGate = false;
        $client = new GuzzleClient($sandBox);
        $zarinGatePSP = '';
        $lang = 'fa';
        $zarinpal = new Zarinpal($merchantId, $client, $lang, $sandBox, $zarinpalGate, $zarinGatePSP);
        $payment = [
            'callback_url' => route('customer.sales-process.payment-callback', ['order' => $order, 'onlinePayment' => $onlinePayment]),
            'amount' => (int) $amount * 10,
            'description' => 'The Order'
        ];
        try {
            $response = $zarinpal->request($payment);
            $code = $response['data']['code'];
            $message = $zarinpal->getCodeMessage($code);
            if ($code == 100) {
                $onlinePayment->update([
                    'bank_first_response' => $response
                ]);
                $authority = $response['data']['authority'];
                return redirect($authority);
            }
        } catch (RequestException $exception) {

            echo $exception;
            return false;
        }
    }

    public function zarinpalVerify($amount, $onlinePayment)
    {
        $authority = $_GET['Authority'];
        $data = [
            'merchantId' => Config::get('payment.zarinpal_merchant_id'),
            'authority' => $authority,
            'amount' => (int) $amount * 10
        ];
        if (OnlinePayment::where('bank_second_response->data->authority', $authority)->exists()) {
            return ['success' => false]; // جلوگیری از ارسال دوباره تراکنش قبلی
        }
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        $onlinePayment->update([
            'bank_second_response' => $result
        ]);
        if (count($result['errors']) === 0) {
            if ($result['data']['code'] == 100) {
                return ['success' => true];
            } else {
                return ['success' => false]; //not means failed but although means everythings except 100
            }
        } else {
            return ['success' => false];
        }
    }

    function resultCodes($code)
    {
        switch ($code) {
            case 100:
                return "با موفقیت تایید شد";

            case 102:
                return "merchant یافت نشد";

            case 103:
                return "merchant غیرفعال";

            case 104:
                return "merchant نامعتبر";
                
            case 105:
                return "amount بایستی بزرگتر از 1,000 ریال باشد";

            case 106:
                return "callbackUrl نامعتبر می‌باشد. (شروع با http و یا https)";

            case 113:
                return "amount مبلغ تراکنش از سقف میزان تراکنش بیشتر است.";

            case 201:
                return "قبلا تایید شده";

            case 202:
                return "سفارش پرداخت نشده یا ناموفق بوده است";

            case 203:
                return "trackId نامعتبر می‌باشد";

            default:
                return "وضعیت مشخص شده معتبر نیست";
        }
    }


    function statusCodes($code)
    {
        switch ($code) {
            case -1:
                return "در انتظار پردخت";

            case -2:
                return "خطای داخلی";

            case 1:
                return "پرداخت شده - تاییدشده";

            case 2:
                return "پرداخت شده - تاییدنشده";

            case 3:
                return "لغوشده توسط کاربر";

            case 4:
                return "‌شماره کارت نامعتبر می‌باشد";

            case 5:
                return "‌موجودی حساب کافی نمی‌باشد";

            case 6:
                return "رمز واردشده اشتباه می‌باشد";

            case 7:
                return "‌تعداد درخواست‌ها بیش از حد مجاز می‌باشد";

            case 8:
                return "‌تعداد پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد";

            case 9:
                return "مبلغ پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد";

            case 10:
                return "‌صادرکننده‌ی کارت نامعتبر می‌باشد";

            case 11:
                return "خطای سوییچ";

            case 12:
                return "کارت قابل دسترسی نمی‌باشد";

            default:
                return "وضعیت مشخص شده معتبر نیست";
        }
    }
}