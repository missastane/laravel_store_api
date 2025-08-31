<?php

namespace App\Http\Requests\Customer\SalesProcess;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChooseAddressAndDeliveryRequest extends FormRequest
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
            'address_id' => ['required',Rule::exists('addresses','id')->where('user_id',request()->user()->id)],
            'delivery_id' => 'required|exists:delivery,id',
        ];
    }

    public function attributes()
    {
        return[
            'address_id'=> 'آدرس',
            'delivery_id' => 'روش ارسال'
        ];
    }
}
