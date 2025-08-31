<?php

namespace App\Http\Requests\Admin\Market;

use App\Rules\MetaKeyBeforeValue;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $rules = [
            'name' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،\- ]+$/u',
            'introduction' => 'required|min:5',
            'status' => 'required|numeric|in:1,2',
            'marketable' => 'required|numeric|in:1,2',
            'weight' => 'required|max:1000|numeric|regex:/^[0-9.]+$/u',
            'width' => 'required|max:1000|numeric|regex:/^[0-9.]+$/u',
            'length' => 'required|max:1000|numeric|regex:/^[0-9.]+$/u',
            'height' => 'required|max:1000|numeric|regex:/^[0-9.]+$/u',
            'price' => 'required|numeric|regex:/^[0-9.]+$/u',
            'tags.*' => 'string|max:255|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,،_\.?؟ ]+$/u',
            'tags' => 'required|array|min:1',
            'related_products.*' => 'numeric|exists:products,id',
            'related_products' => 'required|array|min:1',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'required|exists:product_categories,id',
            'published_at' => 'required|numeric',
            // 'g-recaptcha-response' => 'recaptcha',
        ];
    
    
        if ($this->isMethod('post')) {
         
            $rules['image'] = 'required|image|mimes:png,jpg,jpeg,gif';
        } else {
          
            $rules['image'] = 'image|mimes:png,jpg,jpeg,gif';
        }
    
     
        if ($this->has('meta_key') || $this->has('meta_value')) {
            $rules['meta_key'] = 'array';
            $rules['meta_value'] = 'array';
            $rules['meta_key.*'] = ['nullable', 'string', new MetaKeyBeforeValue]; 
            $rules['meta_value.*'] = ['nullable', 'string', new MetaKeyBeforeValue]; 
        }
    
        return $rules;
    }

    public function attributes()
    {
        return[
            'name'=> 'نام کالا',
            'category_id'=> 'دسته کالا',
            'brand_id'=> 'برند کالا',
            'price'=> 'قیمت کالا',
            'related_products'=> 'محصولات مرتبط',
        ];
    }
}
