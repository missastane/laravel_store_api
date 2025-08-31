<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class OrderFilterRequest extends FormRequest
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
            'order_status' => ['nullable', 'string', 'regex:/^(unseen|processing|not-approved|approved|canceled|returned)(, *(unseen|processing|not-approved|approved|canceled|returned))*$/'],
            'payment_status' => ['nullable', 'string', 'regex:/^(unpaid|paid|canceled|returned)(, *(unpaid|paid|canceled|returned))*$/'],
            'delivery_status' => ['nullable', 'string', 'regex:/^(not_sending|sending|sent|delivered)(, *(not_sending|sending|sent|delivered))*$/'],
        ];
    }

    public function messages()
    {
        return [
            'order_status.regex' => 'باشد یا اینکه چند مورد بوسیله کاما از هم جدا شده باشد unseen, processing, not-approved, approved, canceled, returned فیلد وضعیت سفارش باید شامل یکی از موارد',
            'payment_status.regex' => 'باشد یا چند مورد به وسیله کاما از هم جدا شده باشد unpaid, paid, canceled, returned فیلد وضعیت پرداخت  باید یکی از موارد',
            'delivery_status.regex' => 'باشد یا چند مورد به وسیله کاما از هم جدا شده باشد not_sending, sending, sent, delivered فیلد وضعیت تحویل سفارش',
        ];
    }
}
