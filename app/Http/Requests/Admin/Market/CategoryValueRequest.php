<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class CategoryValueRequest extends FormRequest
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
            'value' => 'required|max:120|min:1|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،::. ]+$/u',
            'price_increase' => 'required|regex:/^[0-9\.]+$/u',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|numeric|in:1,2',
            // 'g-recaptcha-response' => 'recaptcha',
            
        ];
    }
}
