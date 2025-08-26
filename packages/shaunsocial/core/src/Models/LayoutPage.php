<?php


namespace Packages\ShaunSocial\Core\Models;

use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Packages\ShaunSocial\Core\Models\LayoutContent;

class LayoutPage extends Model
{
    use HasCacheQueryFields;
    use HasTranslations;
    protected $translatable = [
        'title',
    ];

    protected $cacheQueryFields = [
        'type',
        'router',
        'page_id'
    ];

    protected $fillable = [
        'type',
        'title',
        'component',
        'meta_keywords',
        'meta_description',
        'router',
        'page_id'
    ];

    public function getContents($viewType = '')
    {
        return LayoutContent::getContents($this->id, $viewType);
    }

    protected static function booted()
    {
        parent::booted();

        static::deleted(function ($page) {
            LayoutContent::where('page_id', $page->id)->get()->each(function($content) {
                $content->delete();
            });
        });
    }
}
