<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
            
            'title' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9-۰-۹ء-ي,،?؟!\. ]+$/u',
            'description' => 'required|max:300|min:5',
            'icon' => 'image|mimes:png,jpg,jpeg,gif',
            'logo' => 'image|mimes:png,jpg,jpeg,gif',
            'keywords.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9-۰-۹ء-ي.,،_\?؟ ]+$/u',
            'keywords' => 'required|array|min:1',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }
}
