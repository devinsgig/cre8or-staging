<?php


namespace Packages\ShaunSocial\Core\Validation;

use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\PhoneNumberUtil;

class PhoneValidation implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            if (! $phoneUtil->isValidNumber($phoneUtil->parse($value))) {
                $fail(__('The phone number is in an invalid format.'));
            }
        } catch (Exception $e) {
            $fail(__('The phone number is in an invalid format.'));
        }
        
    }
}
