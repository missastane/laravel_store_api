<?php

namespace App\Http\Requests\Customer;

use App\Rules\NationalCode;
use App\Rules\UniquePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserProfileRequest extends FormRequest
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
            'first_name' => ['sometimes','max:120','min:2','regex:/^[ا-یa-zA-Z\-ء-ي ]+$/u',Rule::requiredIf(auth()->user()->first_name === null)],
            'last_name' => ['sometimes','max:120','min:2','regex:/^[ا-یa-zA-Z\-ء-ي ]+$/u',Rule::requiredIf(auth()->user()->last_name === null)],
            'email' => ['sometimes','email','unique:users,email',Rule::requiredIf(auth()->user()->email === null)],
            'mobile'=> ['sometimes','min:10','max:13',new UniquePhoneNumber,Rule::requiredIf(auth()->user()->mobile === null)],
            'national_code'=> ['sometimes','unique:users,national_code', new NationalCode(),Rule::requiredIf(auth()->user()->national_code === null)]
        ];
    }
}
