<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
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
                'title' => 'required|max:120|min:2',
                'url' => 'required|url',
                'status' => 'required|numeric|in:1,2',
                'position' => 'required|numeric',
                'image' => 'required|image|mimes:png,jpg,jpeg,gif',
                // 'g-recaptcha-response' => 'recaptcha',
            ];
        } else {
            return [
                'title' => 'required|max:120|min:2',
                'url' => 'required|url',
                'status' => 'required|numeric|in:1,2',
                'position' => 'required|numeric',
                'image' => 'image|mimes:png,jpg,jpeg,gif',
                // 'g-recaptcha-response' => 'recaptcha',
            ];
        }

    }
}
