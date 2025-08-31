<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|exists:users,email',
            'token' => 'required',
            'password' => ['required', 'unique:users', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(), 'confirmed'],
        ];
    }
    public function messages()
    {
        return [
            'password.letters' => 'رمز عبور باید شامل حروف باشد',
            'password.mixed' => 'رمز عبور باید حروف بزرگ و کوچک داشته باشد',
            'password.numbers' => 'رمز عبور باید شامل اعداد باشد',
            'password.symbols' => 'رمز عبور باید شامل نمادها باشد',
            'password.uncompromised' => 'رمز عبور شما در معرض خطر است',
        ];
    }
}
