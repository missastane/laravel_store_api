<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
                'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
                'description' => 'required|max:300|min:5',
                'status' => 'required|numeric|in:1,2',
                'show_in_menu' => 'required|numeric|in:1,2',
                'parent_id' => 'nullable|exists:product_categories,id',
                'image' => 'required|image|mimes:png,jpg,jpeg,gif',
                'tags.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
                'tags' => 'required|array|min:1',
            ];
        } else {
            return [
                'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',
                'description' => 'required|max:300|min:5',
                'status' => 'required|numeric|in:1,2',
                'show_in_menu' => 'required|numeric|in:1,2',
                'parent_id' => 'nullable|exists:product_categories,id',
                'image' => 'image|mimes:png,jpg,jpeg,gif',
                'tags.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
                'tags' => 'required|array|min:1',
            ];
        }
    }
}
