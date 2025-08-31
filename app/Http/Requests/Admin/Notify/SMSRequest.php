<?php

namespace App\Http\Requests\Admin\Notify;

use Illuminate\Foundation\Http\FormRequest;

class SMSRequest extends FormRequest
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
            'title' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.!، ]+$/u',
            'body' => 'required|min:10|max:250|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.!، ]+$/u',
            'published_at' => 'required|numeric',
            'status' => 'required|numeric|in:1,2',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }
}
