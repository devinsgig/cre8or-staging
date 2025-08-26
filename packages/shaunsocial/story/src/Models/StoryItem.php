<?php


namespace Packages\ShaunSocial\Story\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasReport;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Traits\IsSubjectNotification;
use Packages\ShaunSocial\Story\Http\Resources\StoryItemResource;

class StoryItem extends Model
{
    use HasCacheQueryFields, HasStorageFiles, HasUser, HasCachePagination, IsSubject, HasReport, IsSubjectNotification;

    public function getListCachePagination()
    {
        return [
            'story_item_user_'.$this->user_id
        ];
    }

    protected $cacheQueryFields = [
        'id',
        'story_id',
    ];
    
    protected $storageFields = [
        'photo_id',
    ];

    protected $fillable = [
        'user_id',
        'story_id',
        'type',
        'background_id',
        'content',
        'song_id',
        'photo_id',
        'content_color'
    ];

    static public function getTypes()
    {
        return [
            'text',
            'photo'
        ];
    }

    public function getBackground()
    {
        if ($this->background_id) {
            return StoryBackground::findByField('id', $this->background_id);
        }
        return null;
    }

    public function getSong()
    {
        if ($this->song_id) {
            return StorySong::findByField('id', $this->song_id);
        }
        return null;
    }

    public function getStory()
    {
        return Story::findByField('id', $this->story_id);
    }

    public function getPhoto()
    {
        if ($this->photo_id) {
            return $this->getFile('photo_id');
        }
        return null;
    }

    public function getView($userId)
    {
        return StoryView::getView($userId, $this->id);
    }

    public function canDelete($userId)
    {
        return $this->isOwner($userId);
    }

    public function canReport($userId)
    {
        return !$this->isOwner($userId);
    }

    public static function getResourceClass()
    {
        return StoryItemResource::class;
    }

    public function getTitle()
    {
        return __('Story of').' '.$this->getUser()->getName();
    }

    public function getHref()
    {
        if ($this->id) {
            return route('web.story.detail_item',[
                'id' => $this->id
            ]);
        }
        
        return  '';
    }

    public function canShare()
    {
        return $this->story_id ? true : false;
    }

    protected static function booted()
    {
        static::deleted(function ($item) {
            StoryView::where('story_item_id', $item->id)->delete();
        });
    }
}
