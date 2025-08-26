<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Models\Gender;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Http\Requests\Utility\CountryValidate;

class StoreEditProfileValidate extends UserValidate
{
    public function rules()
    {
        $timezoneList = getTimezoneList();
        $rules = [
            'name' => 'required|string|max:64',
            'bio' => 'string|nullable|max:'.config('shaun_core.core.user_bio_limit'),
            'about' => 'string|nullable|max:'.config('shaun_core.core.user_about_limit'),
            'birthday' => 'nullable|date_format:Y-m-d|before:today',
            'links' => [
                'string',
                'nullable',
                function ($attribute, $links, $fail) {
                    $links = Str::of($links)->explode(' ')->filter(function ($value) {
                        return !empty($value);
                    });
                    
                    foreach ($links as $link) {                        
                        $validator = Validator::make(['link' => trim($link)],[
                            'link' => 'url'
                        ]);

                        if ($validator->fails()) {
                            return $fail(__('The link should be valid.'));
                        }
                    }
                },
            ],
            'gender_id' => [
                'nullable',
                Rule::in(array_keys(Gender::getAllKeyValue()))
            ],
            'timezone' => [
                'required',
                'string',
                Rule::in(array_keys($timezoneList))
            ],
            'privacyField' => [
                'nullable',
                function ($attribute, $privacyField, $fail) {
                    if ($privacyField && ! is_array($privacyField)) {
                        return $fail(__('The privacy field is required.'));
                    }
                },
            ]
        ];

        if (setting('feature.require_birth')) {
            $rules['birthday'] = [
                'required', 
                'date_format:Y-m-d',
                'before:today',
                function ($attribute, $birthday, $fail) {
                    if ($birthday && setting('feature.age_restriction')) {
                        $age = Carbon::parse(date('Y-01-01',strtotime($birthday)))->age;
                        if ($age < setting('feature.age_restriction')) {
                            return $fail(__('You must be :1 years of age or older.', ['1' => setting('feature.age_restriction')]));
                        }
                    }
                },
            ];
        }
        if (setting('feature.require_gender')) {
            $rules['gender_id'] = [
                'required',
                Rule::in(array_keys(Gender::getAllKeyValue()))
            ];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (setting('feature.require_location')) {
                CountryValidate::$required = true;
            }

            $result = CountryValidate::runFormRequest($this->request->all());
            
            if ($result->fails()) {
                foreach ($result->getMessageBag()->getMessages() as $key => $messages) {
                    $validator->errors()->add($key, $messages[0]);
                }

                return;
            }
        });

        return $validator;
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 64 characters.'),
            'bio.max' => __('The bio must not be greater than :max characters.', ['max' => config('shaun_core.core.user_bio_limit')]),
            'location.max' => __('The location must not be greater than 255 characters.'),
            'timezone.required' => __('The timezone is required.'),
            'timezone.in' => __('The timezone is not in the list.'),
            'birthday.required' => __('The birthday is required.'),
            'birthday.date_format' => __('The birthday format is invalid.'),
            'birthday.before' => __('The birthday cannot be a future date.'),
            'gender_id.in' => __('The gender is required.'),
            'gender_id.required' => __('The gender is required.'),
            'about.max' => __('The about must not be greater than :max characters.', ['max' => config('shaun_core.core.user_about_limit')]),
            'privacyField.required' => __('The privacy field is required.')
        ];
    }
}
