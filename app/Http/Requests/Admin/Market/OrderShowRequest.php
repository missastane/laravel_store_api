<?php

namespace App\Http\Requests\Admin\Market;

use Illuminate\Foundation\Http\FormRequest;

class OrderShowRequest extends FormRequest
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
            'include' => ['nullable', 'string', function ($attribute, $value, $fail) {
                $allowedRelations = [
                    'address', 'payment', 'delivery', 'copan', 
                    'commonDiscount', 'orderItems', 'orderItems.product', 'orderItems.amazingSale'
                ];
                $relations = explode(',', $value);
                
                foreach ($relations as $relation) {
                    if (!in_array($relation, $allowedRelations)) {
                        $fail(" نامعتبر است '{$relation}' رابطه");
                    }
                }
            }]
        ];
    }
}
