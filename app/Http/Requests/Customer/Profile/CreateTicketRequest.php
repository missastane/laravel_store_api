<?php

namespace App\Http\Requests\Customer\Profile;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'subject'=>'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.! ]+$/u',
            'description'=>'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.! ]+$/u',
            'category_id' => 'required|exists:ticket_categories,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'file' => 'nullable|mimes:png,jpg,jpeg,gif,zip,pdf,doc,docx',
        ];
    }

    public function attributes()
    {
        return[
            'subject' => 'عنوان تیکت',
            'category_id' => 'دسته تیکت',
            'priority_id' => 'اولویت تیکت',
            'description' => 'متن تیکت',
            'file' => 'فایل',
        ];
    }
}
