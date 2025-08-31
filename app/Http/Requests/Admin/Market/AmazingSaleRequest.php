<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class AmazingSaleRequest extends FormRequest
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
            'percentage' => 'required|numeric|min:0|max:100',
            'product_id'=> 'required|exists:products,id',
            'start_date' => 'required|numeric',
            'end_date' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }

    public function attributes()
    {
        return [
            "percentage" => "درصد تخفیف",
            "product_id" => "نام کالا",
            "start_date" => "تاریخ شروع اعتبار کوپن",
            "end_date" => "تاریخ انقضای کوپن",
        ];
    }
}
