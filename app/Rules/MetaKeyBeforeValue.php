<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MetaKeyBeforeValue implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $metaKeys = request()->input('meta_key', []);
        $metaValues = request()->input('meta_value', []);
    
        if (count($metaKeys) !== count($metaValues)) {
            $fail('تعداد ویژگی ها و مقدار آن ها باید برابر باشد.');
            return;
        }
    
        foreach ($metaValues as $index => $metaValue) {
            if (!isset($metaKeys[$index])) {
                $fail("هر مقدار باید بعد از ویژگی مربوط به خودش ارسال شود.");
                return;
            }
        }
    }
    
}