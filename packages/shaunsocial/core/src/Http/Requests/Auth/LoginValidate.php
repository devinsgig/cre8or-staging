<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Auth;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Traits\Utility;

class LoginValidate extends BaseFormRequest
{
    use Utility;
    protected $spamValidate = true;
    
    public function rules()
    {
        return [
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        if ($this->spamValidate && setting('spam.login_enable_recapcha')) {
            if (! $validator->fails()) {
                $validator->after(function ($validator) {
                    $result = $this->validateSpam($this->request->all());
                    if (! $result['status']) {
                        throw new MessageHttpException($result['message']); 
                    }
                });
            }
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'email.required' => __('The email is required.'),
            'password.required' => __('The password is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.exists' => __('The email or password was incorrect.'),
        ];
    }
}
