<?php

namespace App\Http\Requests\Customer\Profile;

use App\Rules\NationalCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => 'nullable|max:120|min:2|regex:/^[ا-یa-zA-Z\-ء-ي ]+$/u',
            'last_name' => 'nullable|max:120|min:2|regex:/^[ا-یa-zA-Z\-ء-ي ]+$/u',
            'national_code'=> ['nullable', new NationalCode(), Rule::unique('users')->ignore($this->user()->national_code, 'national_code')]
        ];
    }
}
