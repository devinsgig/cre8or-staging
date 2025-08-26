<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Hashtag\HashtagResource;
use Packages\ShaunSocial\UserPage\Http\Resources\UserPageCategoryProfileResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $followed = false;
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = false;
        
        if ($viewer) {
            $followed = $viewer->getFollow($this->id) ? true : false;
            $isAdmin = $viewer->isModerator();
        }

        $canShowFollower = $this->canViewFollower($viewerId) || $isAdmin;
        $canFollow = $this->canFollow($viewerId) || ($isAdmin && $viewerId != $this->id);

        $result = [
            'id' => $this->id,
            'name' => $this->getName(),
            'user_name' => $this->user_name,
            'cover' => $this->getCover(),
            'avatar' => $this->getAvatar(),
            'bio' => $this->bio,
            'is_followed' => $followed,
            'show_follower' => $canShowFollower,
            'can_follow' => $canFollow,
            'href' => $this->getHref(),
            'is_verify' => $this->isVerify(),
            'is_page' => $this->isPage(),
        ];

        if ($this->isPage()) {
            $result += [
                'categories' => UserPageCategoryProfileResource::collection($this->getCategories()),
                'page_hashtags' => HashtagResource::collection($this->getPageHashtags()),
                'is_page_feature' => $this->is_page_feature
            ];
        } 

        if ($canShowFollower) {
            $result += ['follower_count' => $this->follower_count];
        }

        $result['badge'] = $this->getSubscriptionBadge();

        return $result;
    }
}
