<?php


namespace Packages\ShaunSocial\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Chat\Models\ChatMessageUser;

class ChatMessageUserResource extends JsonResource
{
    public function toArray($request)
    {        
        $viewer = $request->user();
        $user = $this->getUser();
        $getLastMessageIdSeen = ChatMessageUser::getLastMessageIdSeen($this->room_id, $this->user_id);

        return [
            'id' => $user->id,
            'name' => $user->getName(),
            'is_online' => $user->isOnline($viewer->id),
            'user_name' => $user->user_name,
            'avatar' => $user->getAvatar(),
            'message_seen_id'  => $getLastMessageIdSeen ? $getLastMessageIdSeen->message_id : null,
            'is_verify' => $user->isVerify()
        ];
    }
}
