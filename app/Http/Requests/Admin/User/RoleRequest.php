<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
        $route = Route::current();
        if($route->getName() === 'admin.user.role.store'){
            return [
                'name' => ['required','max:120','min:2','regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',Rule::unique('roles','name')->ignore($this->route('role'))],
                'status' => 'required|numeric|in:1,2',
                'description' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.! ]+$/u',
                'permission_id.*' => 'exists:permissions,id',
                // 'g-recaptcha-response' => 'recaptcha',
            ];
        }
        elseif($route->getName() === 'admin.user.role.update')
        {
            return [
                'name' => ['required','max:120','min:2','regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,، ]+$/u',Rule::unique('roles','name')->ignore($this->route('role'))],
                'status' => 'required|numeric|in:1,2',
                'description' => 'required|max:120|min:2|regex:/^[ا-یa-zA-Z0-9\-۰-۹ء-ي.,؟?_\.! ]+$/u',
                // 'g-recaptcha-response' => 'recaptcha',
            ];
        }
        elseif($route->getName() === 'admin.user.role.permission')
        {
            return [
                
                 'permission_id.*' => 'exists:permissions,id',
                //  'g-recaptcha-response' => 'recaptcha',
            ];
        }
       return[
        // 
       ];
    }

    public function attributes()
    {
        return[
            'name'=> 'عنوان نقش',
            'permission_id.*'=> 'دسترسی',

        ];
    }
}
