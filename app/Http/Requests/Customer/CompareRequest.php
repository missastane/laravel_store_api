<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use function PHPUnit\Framework\returnArgument;

class CompareRequest extends FormRequest
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
        $route = Route::currentRouteName();
        if ($route == 'customer.market.add-to-compare') {
            return [
                'products' => 'required|array|min:1|max:4',
                'products.*' => 'exists:products,id',
            ];
        } elseif ($route == 'customer.market.remove-from-compare') {
            return [
                'products' => 'required|array|max:4|min:2',
                'products.*' => 'exists:products,id',
            ];
        }
       return [
        //
       ];
    }

    public function attributes()
    {
        return [
            'products' => 'محصولات لیست قبلی',
            'new_product' => 'محصول',
            'removed_product' => 'محصول پاک شده',
            'product' => 'محصول',
        ];
    }
}
