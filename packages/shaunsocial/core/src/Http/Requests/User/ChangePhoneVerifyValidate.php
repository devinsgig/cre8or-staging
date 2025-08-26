<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\PhoneVerifyValidate;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PhoneValidation;

class ChangePhoneVerifyValidate extends PhoneVerifyValidate
{
    use Utility;

    public function rules()
    {
        return [
            'phone_number' => [
                'required', 
                new PhoneValidation(),
                function ($attribute, $phoneNumber, $fail) {
                    if ($phoneNumber) {
                        $user = User::where('phone_number', $phoneNumber)->where('phone_verified', true)->first();
                        if ($user) {
                            return $fail(__('The phone number already exists.'));
                        }
                    }
                },
            ]
        ];
    }

    public function prepareForValidation()
    {
        parent::prepareForValidation();
        
        $phoneNumber = $this->input('phone_number');
        if ($phoneNumber) {
            $this->merge([
                'phone_number' => str_replace(' ', '', $phoneNumber)
            ]);
        }
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                if (setting('spam.send_sms_enable_recapcha')) {
                    $result = $this->validateSpam($this->request->all());
                    if (! $result['status']) {
                        throw new MessageHttpException($result['message']); 
                    }
                }
                
                $viewer = $this->user();
                if ($viewer->phone_verified) {
                    return $validator->errors()->add('user', __('You have verified your phone.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'phone_number.required' => __('The phone number is required.'),
        ];
    }
}
