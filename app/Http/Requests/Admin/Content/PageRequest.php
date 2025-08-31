<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
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
            'title' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,_\.! ]+$/u',
            'body' => 'required|min:10',
            'status' => 'required|numeric|in:1,2',
            'tags.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ !]+$/u',
            'tags' => 'required|array|min:1',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }
}
