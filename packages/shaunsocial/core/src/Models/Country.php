<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class Country extends Model
{
    use HasTranslations, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $translatable = [
        'name'
    ];
    
    protected $fillable = [
        'name',
        'country_iso',
        'state_count',
        'order',
        'is_active'
    ];

    public static function getAll()
    {
        return Cache::rememberForever('countries', function () {
            return self::orderBy('order')->where('is_active', true)->orderBy('id','DESC')->get();
        });
    }

    public function clearCacheTranslate()
    {
        $this->clearCache();
    }

    public function clearCache()
    {
        Cache::forget('countries');
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($country) {
            $country->clearCache();

            $states = State::where('country_id', $country->id)->get();
            $states->each(function($state) {
                $state->delete();
            });
        });

        static::saved(function ($country) {
            $country->clearCache();
        });
    }
}

