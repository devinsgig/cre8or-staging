<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StoreWebsitesValidate extends PageValidate
{
    public function rules()
    {
        return [
            'websites' => [
                'string',
                'nullable',
                function ($attribute, $websites, $fail) {
                    $websites = Str::of($websites)->explode(' ')->filter(function ($value) {
                        return !empty($value);
                    });
                    
                    foreach ($websites as $website) {                        
                        $validator = Validator::make(['website' => trim($website)],[
                            'website' => 'url'
                        ]);

                        if ($validator->fails()) {
                            return $fail(__('The website should be valid.'));
                        }
                    }
                },
            ],
        ];
    }
}
