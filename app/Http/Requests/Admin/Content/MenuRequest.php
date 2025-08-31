<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
            'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,_\. ]+$/u',
            'status' => 'required|numeric|in:1,2',
            'url' => 'required|url',
            'parent_id' => 'nullable|max:100000000|regex:/^[0-9]+$/u|exists:menus,id',
            // 'g-recaptcha-response' => 'recaptcha',

        ];
    }
}
