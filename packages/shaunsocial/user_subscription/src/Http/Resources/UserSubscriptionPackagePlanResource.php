<?php


namespace Packages\ShaunSocial\UserSubscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionPackagePlanResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();

        return [
            'id' => $this->id,
            'trial_day' => $viewer->user_subscription_has_trial ? 0 : $this->trial_day,
            'google_price_id' => $this->google_price_id,
            'apple_price_id' => $this->apple_price_id,
            'description' => $this->getDescription(),
            'name' => $this->getTranslatedAttributeValue('name'),
        ];
    }
}
