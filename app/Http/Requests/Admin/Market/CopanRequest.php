<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class CopanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'amount_type' => 'required|numeric|in:1,2',
            'amount' => [request()->amount_type == 0 ? 'max:100' : '','required','numeric','regex:/^[0-9.]+$/u'],
            'type' => 'required|numeric|in:1,2',
            'discount_ceiling' => 'required|numeric|regex:/^[0-9.]+$/u',
            'user_id' => 'required_if:type,==,1|exists:users,id',
            'start_date' => 'required|numeric',
            'end_date' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }


    public function attributes()
    {
        return [
            "percentage" => "درصد تخفیف",
            "user_id" => "نام کاربر",
            "code" => "کد تخفیف",
            "amount" => "میزان تخفیف",
            "amount_type" => "نوع تخفیف",
            "discount_ceiling" => "سقف تخفیف",
            "type" => "نوع کوپن",
            "start_date" => "تاریخ شروع اعتبار کوپن",
            "end_date" => "تاریخ انقضای کوپن",
        ];
    }
}
