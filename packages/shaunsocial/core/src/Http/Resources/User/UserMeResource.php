<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageResource;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscription;

class UserMeResource extends JsonResource
{
    public function toArray($request)
    {
        $languageKey = App::getLocale();
        $language = Language::getBykey($languageKey);
        $canDelete = $this->canDelete() && setting('user.allow_delete');
        if ($this->isPage()) {
            $canDelete = $canDelete && $this->isPageOwner();
        }
        $videoAutoPlay = $this->id ? $this->video_auto_play : true;
        $isAdmin = $this->id ? $this->isModerator() : false;

        $result = [
            'id' => $this->id ?? 0,
            'email' => ! $this->isPage() ? $this->email : '',
            'name' => $this->getName(),
            'user_name' => $this->user_name,
            'role_id' => $this->role_id,
            'avatar' => $this->getAvatar(),
            'cover' => $this->getCover(),
            'follower_count' => $this->follower_count,
            'following_count' => $this->following_count,
            'ref_url' => $this->getRefUrl(),
            'href' => $this->getHref(),
            'already_setup_login' => $this->already_setup_login,
            'email_verified' => $isAdmin || $this->isPage() ? true : $this->email_verified,
            'phone_number' => $this->phone_number,
            'phone_verified' => $isAdmin || $this->isPage() ? true : $this->phone_verified,
            'darkmode' => $this->darkmode,
            'can_delete' => $canDelete ? true : false,
            'language' => $languageKey,
            'rtl' => $language->is_rtl ? true : false,
            'is_moderator' => $this->id ? $this->isModerator() : false,
            'video_auto_play' => $videoAutoPlay,
            'has_email' => $this->has_email,
            'timezone' => $this->id ? $this->timezone : setting('site.timezone'),
            'permissions' => $this->getPermissionsForApi(),
            'is_verify' => $this->isVerify(),
            'wallet_balance' => $this->getCurrentBalance(),
            'is_page' => $this->isPage(),
            'can_show_withdraw_wallet' => $this->canShowWithdrawWallet(),
            'can_show_send_wallet' => $this->canShowSendWallet(),
            'can_show_creator_dashboard' => $this->canShowCreatorDashBoard(),
            'can_create_post_paid_content' => $this->canCreatePostPaidContent()
        ];

        $parent = null;
        if ($this->id) {
            if ($this->isPage()) {
                $parent = new UserPageResource($this->getParent());
                $result['page_owner'] = new UserResource($this->getOwnerPage()->getUser());
                $result['is_owner_page'] = $this->getParent()->id == $this->getOwnerPage()->getUser()->id;
                $result['is_page_feature'] = $this->is_page_feature;
            }
        }

        $result['parent'] = $parent;

        if ($result['is_moderator']){
            $result['admin_link'] = route('admin.dashboard.index');
        }

        if (setting('shaun_user_subscription.enable') && $this->id) {
            $packageName = '';
            $userSubscription = UserSubscription::getActive($this->id);
            if ($userSubscription) {
                $subscription = $userSubscription->getSubscription();
                $packageName = $subscription->package_name;
            }
            $result['membership_package_name'] = $packageName;
        }

        return $result;
    }
}
