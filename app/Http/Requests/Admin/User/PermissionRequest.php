<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
            'name' => ['required','max:120','min:2','regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',Rule::unique('permissions','name')->ignore($this->route('permission'))],
            'status' => 'required|numeric|in:1,2',
            'description' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.! ]+$/u',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    }
}
