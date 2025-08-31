<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'persian_name' => 'required|max:120|min:2|regex:/^[ا-ی\-۰-۹ء-ي.،0-9 ]+$/u',
                'original_name' => 'required|max:120|min:2|regex:/^[a-zA-Z0-9\, ]+$/u',
                'status' => 'required|numeric|in:1,2',
                'logo' => 'required|image|mimes:png,jpg,jpeg,gif',
                'tags.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
                'tags' => 'required|array|min:1',
                // 'g-recaptcha-response' => 'recaptcha',
            ];
        } else {
            return [
                'persian_name' => 'required|max:120|min:2|regex:/^[ا-ی\-۰-۹ء-ي.،0-9 ]+$/u',
                'original_name' => 'required|max:120|min:2|regex:/^[a-zA-Z0-9\, ]+$/u',
                'status' => 'required|numeric|in:1,2',
                'logo' => 'nullable|image|mimes:png,jpg,jpeg,gif',
                'tags.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
                'tags' => 'required|array|min:1',
                // 'g-recaptcha-response' => 'recaptcha',
            ];
        }
    }
}
