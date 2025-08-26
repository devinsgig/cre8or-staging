<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AdminLoginValidate extends LoginValidate
{
    protected $redirector = 'admin.auth.index';
    protected $spamValidate = false;

    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }
}
