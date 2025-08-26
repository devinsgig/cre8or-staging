<?php


namespace Packages\ShaunSocial\Story\Repositories\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Chat\Models\ChatMessageItem;
use Packages\ShaunSocial\Chat\Repositories\Api\ChatRepository;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserFollowNotificationCron;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Story\Http\Resources\StoryDetailResource;
use Packages\ShaunSocial\Story\Http\Resources\StoryItemResource;
use Packages\ShaunSocial\Story\Http\Resources\StoryResource;
use Packages\ShaunSocial\Story\Http\Resources\StorySongResource;
use Packages\ShaunSocial\Story\Models\Story;
use Packages\ShaunSocial\Story\Models\StoryItem;
use Packages\ShaunSocial\Story\Models\StorySong;
use Packages\ShaunSocial\Story\Models\StoryView;
use Packages\ShaunSocial\Story\Notification\StoryEndNotification;
use Packages\ShaunSocial\Story\Notification\StoryUserFollowNotification;

class StoryRepository
{
    use HasUserList;
    
    protected $chatRepository;

    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function search_song($query)
    {
        $songs = Cache::remember('song_search_'.$query, config('shaun_core.cache.time.short'), function () use ($query) {
            return StorySong::getCacheSearch('name_'.$query, StorySong::where('name', 'LIKE', '%'.$query.'%')->where('is_active', true)->orderBy(DB::raw("LOCATE('".$query."', name)"))->limit(setting('feature.item_per_page')));
        });
        
        return StorySongResource::collection($songs);
    }

    public function get($viewer, $page)
    {
        $builder = Story::orderBy('last_updated_at', 'DESC')->where('user_id', '!=' , $viewer->id)->whereIn('user_privacy', [
            config('shaun_core.privacy.user.everyone'),
            config('shaun_core.privacy.user.my_follower'),
        ]);

        if ($viewer->following_count) {
            if ($viewer->following_count > config('shaun_core.follow.user.max_query_join')) {
                $builder->whereIn('user_id', function($select) use ($viewer) {
                    $select->from('user_follows')
                        ->select('follower_id')
                        ->where('user_id', $viewer->id);
                });
            } else {
                $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                $builder->whereIn('user_id',$userFollowers);        
            }

            $results = Cache::remember('story_'.$viewer->id.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
                return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
            });
        } else {
            $results = collect();
        }

        $story = Story::findByField('user_id', $viewer->id);
        
        if ($story && $page == 1) {
            $results->prepend($story);
        }
        
        return StoryResource::collection($this->filterUserList($results, $viewer));
    }

    public function store($data, $photo ,$viewer)
    {
        $story = Story::findByField('user_id', $viewer->id);
        if (! $story) {
            $story = Story::create([
                'user_id' => $viewer->id,
                'user_privacy' => $viewer->privacy,
                'last_updated_at' => now()
            ]);
        } else {
            $story->update([
                'last_updated_at' => now()
            ]);
        }
        $items = $story->getItems();

        switch ($data['type'])
        {
            case 'text':
                $item = StoryItem::create([
                    'content' => $data['content'],
                    'user_id' => $viewer->id,
                    'story_id' => $story->id,
                    'type' => 'text',
                    'song_id' => $data['song_id'],
                    'background_id' => $data['background_id'],
                    'content_color' => $data['content_color']
                ]);
                break;
            case 'photo':
                $storageFile = File::storePhoto($photo, [
                    'parent_type' => 'story_item_photo',
                    'user_id' => $viewer->id,
                    'extension' => $photo->getClientOriginalExtension(),
                    'name' => $photo->getClientOriginalName()
                ]);
                $item = StoryItem::create([
                    'user_id' => $viewer->id,
                    'story_id' => $story->id,
                    'type' => 'photo',
                    'song_id' => $data['song_id'],
                    'photo_id' => $storageFile->id
                ]);
                break;
        }

        //Send notify to follower
        if ($viewer->checkUserEnableFollowNotification()) {
            UserFollowNotificationCron::create([
                'user_id' => $viewer->id,
                'subject_type' => $item->getSubjectType(),
                'subject_id' => $item->id,
                'class'=> StoryUserFollowNotification::class,
                'package' => 'shaun_story'
            ]);
        }

        if (count($items)) {
            $items->push($item);
        } else {
            $items = collect([$item]);
        }
        
        $story->setItems($items);

        return new StoryResource($story);
    }

    public function detail($id)
    {
        $story = Story::findByField('id', $id);
        return new StoryDetailResource($story);
    }

    public function store_view_item($id, $viewer)
    {
        $storyItem = StoryItem::findByField('id', $id);
        if (! $storyItem->getView($viewer->id)) {
            StoryView::create([
                'user_id' => $viewer->id,
                'story_item_id' => $storyItem->id,
                'story_id' => $storyItem->story_id
            ]);
        }
    }

    public function delete($id, $deleteAuto = false)
    {
        $storyItem = StoryItem::findByField('id', $id);
        $story = $storyItem->getStory();
        if ($story) {
            $items = $story->getItems();
            $items = $items->filter(function ($value, $key) use ($storyItem){
                return $value->id != $storyItem->id;
            });

            if (count($items)) {
                $story->update([
                    'last_updated_at' => $items->last()->created_at
                ]);
            } else {
                $story->delete();
            }
        }
        if ($deleteAuto) {
            //add notify when auto delete
            $user = $storyItem->getUser();
            if ($user && $user->checkNotifySetting('story_end')) {
                Notification::send($user, $user, StoryEndNotification::class, $storyItem, ['is_system' => true], 'shaun_story', false);
            }

            $storyItem->update([
                'story_id' => 0
            ]);
        } else {
            $storyItem->delete();
        }
        
    }

    public function get_view($id, $page, $viewer)
    {
        $storyItem = StoryItem::findByField('id', $id);
        $users = StoryView::getCachePagination('story_viewer_'.$id, StoryView::where('story_item_id', $id)->where('user_id', '!=', $storyItem->user_id), $page);
        $usersNextPage = StoryView::getCachePagination('story_viewer_'.$id, StoryView::where('story_item_id', $id)->where('user_id', '!=', $storyItem->user_id), $page  + 1);

        $users = $users->map(function ($item, $key) {
            return User::findByField('id', $item->user_id);
        });

        return [
            'items' => UserResource::collection($this->filterUserList($users, $viewer, 'id')),
            'has_next_page' => count($usersNextPage) ? true : false
        ];
    }

    public function my($viewer, $page)
    {
        $storyItems = StoryItem::getCachePagination('story_item_user_'.$viewer->id, StoryItem::where('user_id', $viewer->id)->orderBy('id', 'DESC'), $page);
        $storyItemsNextPage = StoryItem::getCachePagination('story_item_user_'.$viewer->id, StoryItem::where('user_id', $viewer->id)->orderBy('id', 'DESC'), $page + 1);

        return [
            'items' => StoryItemResource::collection($storyItems),
            'has_next_page' => count($storyItemsNextPage) ? true : false
        ];
    }

    public function store_message($data, $viewer)
    {
        $storyItem = StoryItem::findByField('id', $data['id']);
        $result = $this->chatRepository->store_room($storyItem->user_id, $viewer);
        
        $item = ChatMessageItem::create([
            'user_id' => $viewer->id,
            'subject_type' => 'story_items',
            'subject_id' => $data['id'],            
        ]);

        $this->chatRepository->store_room_message([
            'type' => 'story_reply',
            'content' => $data['content'],
            'items' => [$item->id],
            'room_id' => $result['id']
        ], $viewer);
    }

    public function detail_item($id, $viewer)
    {
        $storyItem = StoryItem::findByField('id', $id);
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $canSendMessage = $storyItem->getUser()->canSendMessage($viewerId) || $isAdmin;

        return [
            'user' => $storyItem->getUserResource(),
            'item' => new StoryItemResource($storyItem),
            'canMessage' => $viewerId == $storyItem->user_id ? false : $canSendMessage,
            'canShare' => true
        ];
    }

    public function detail_in_list($stortId)
    {
        $story = Story::findByField('id', $stortId);
        
        return new StoryResource($story);
    }

    public function share_message($data, $viewer)
    {
        foreach ($data['user_ids'] as $userId) {
            $result = $this->chatRepository->store_room($userId, $viewer);
            
            $item = ChatMessageItem::create([
                'user_id' => $viewer->id,
                'subject_type' => 'story_items',
                'subject_id' => $data['id'],            
            ]);
    
            $this->chatRepository->store_room_message([
                'type' => 'story_share',
                'content' => $data['content'],
                'items' => [$item->id],
                'room_id' => $result['id']
            ], $viewer);
        }

    }
}
