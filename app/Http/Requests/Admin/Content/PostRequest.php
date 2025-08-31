<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
                'title' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.! ]+$/u',
                'summary' => 'required|max:300|min:5',
                'body' => 'required|min:10',
                'published_at' => 'required|numeric',
                'commentable' => 'required|numeric|in:1,2',
                'post_category_id' => 'required|min:1|max:100000000|regex:/^[0-9]+$/u|exists:post_categories,id',
                'status' => 'required|numeric|in:1,2',
                'image' => 'required|image|mimes:png,jpg,jpeg,gif',
                'tags.*' => 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ !]+$/u',
                'tags' => 'required|array|min:1',
                // 'g-recaptcha-response' => 'recaptcha',

            ];
        } else {
            return [
                'title' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي,?؟_\. ]+$/u',
                'summary' => 'required|max:300|min:5',
                'body' => 'required|min:10',
                'published_at' => 'required|numeric',
                'commentable' => 'required|numeric|in:1,2',
                'post_category_id' => 'required|min:1|max:100000000|regex:/^[0-9]+$/u|exists:post_categories,id',
                'status' => 'required|numeric|in:1,2',
                'image' => 'nullable|image|mimes:png,jpg,jpeg,gif',
                'tags.*' => 'regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
                'tags' => 'required|array|min:1',
                // 'g-recaptcha-response' => 'recaptcha',

            ];
        }

    }
}
