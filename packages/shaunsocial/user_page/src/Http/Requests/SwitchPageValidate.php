<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\Core\Models\User;

class SwitchPageValidate extends UserValidate
{
    public function rules()
    {
        return [
            'id' =>  [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $user = User::findByField('id', $id);

                    if (! $user || ! $user->isPage() || ! $user->getAdminPage($viewer->id)) {
                        return $fail(__('The page is not found.'));
                    }

                    if (! $user->is_active) {
                        return $fail(__('The page is pending approval.'));
                    }
                },
            ]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}