<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Like\LikeResource;
use Packages\ShaunSocial\Core\Models\Like;
use Packages\ShaunSocial\Core\Traits\HasUserList;

class LikeRepository
{
    use HasUserList;

    public function store($data, $viewer)
    {
        $subject = findByTypeId($data['subject_type'], $data['subject_id']);

        switch ($data['action']) {
            case 'add':
                $subject->addLike($viewer->id);
                $subject->sendLikeNotification($viewer);
                break;
            case 'remove':
                $like = $subject->getLike($viewer->id);
                $subject->deleteLikeNotification($viewer);
                $like->delete();
                break;
        }
    }

    public function get($subjectType, $subjectId, $page, $viewer)
    {
        $subject = findByTypeId($subjectType, $subjectId);

        $likes = Like::getCachePagination('like_'.$subjectType.'_'.$subjectId, Like::where('subject_type', $subjectType)->where('subject_id', $subjectId)->orderBy('id', 'DESC'), $page);

        return [
            'items' => LikeResource::collection($this->filterUserList($likes, $viewer)),
            'has_next_page' => checkNextPage($subject->like_count, count($likes), $page)
        ];
    }
}
