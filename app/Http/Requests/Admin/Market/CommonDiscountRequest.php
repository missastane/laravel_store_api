<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class CommonDiscountRequest extends FormRequest
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
            'title' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
            'percentage' => 'required|numeric|min:0|max:100',
            'discount_ceiling' => 'required|numeric|regex:/^[0-9.]+$/u',
            'minimal_order_amount' => 'required|numeric|regex:/^[0-9.]+$/u|min:1',
            'start_date' => 'required|numeric',
            'end_date' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }

    public function attributes()
    {
        return [
            "percentage" => "درصد تخفیف",
            "title" => "مناسبت تخفیف",
            "minimal_order_amount" => "حداقل میزان سفارش",
            "discount_ceiling" => "سقف تخفیف",
            "start_date" => "تاریخ شروع اعتبار کوپن",
            "end_date" => "تاریخ انقضای کوپن",
        ];
    }
}
