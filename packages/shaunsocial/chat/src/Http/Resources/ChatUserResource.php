<?php


namespace Packages\ShaunSocial\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatUserResource extends JsonResource
{
    public function toArray($request)
    {        
        $viewer = $request->user();
        return [
            'id' => $this->id,
            'name' => $this->getName(),
            'is_online' => $this->isOnline($viewer ? $viewer->id : 0),
            'user_name' => $this->user_name,
            'avatar' => $this->getAvatar(),
            'href' => $this->getHref(),
            'is_verify' => $this->isVerify()
        ];
    }
}
