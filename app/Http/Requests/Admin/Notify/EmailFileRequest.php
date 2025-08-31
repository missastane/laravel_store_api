<?php

namespace App\Http\Requests\Admin\Notify;

use Illuminate\Foundation\Http\FormRequest;

class EmailFileRequest extends FormRequest
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
                    'status' => 'required|numeric|in:1,2',
                    'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u',
                    'file' => 'required|mimes:png,jpg,jpeg,gif,zip,pdf,doc,docx',
                    'path'=> 'required|numeric|in:1,2',
                    // 'g-recaptcha-response' => 'recaptcha',
                ];
            }
            else{
                return[
                    'status' => 'required|numeric|in:1,2',
                    'file' => 'mimes:png,jpg,jpeg,gif,zip,pdf,doc,docx',
                    'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي ]+$/u',
                    'path'=> 'required|numeric|in:1,2',
                    // 'g-recaptcha-response' => 'recaptcha',
                ];
            }
        
    }
}
