<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class City extends Model
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
        'city_code',
        'country_id',
        'state_id',
        'order',
        'is_active'
    ];

    public static function getCitiesByStateId($stateId)
    {
        return Cache::rememberForever(self::getCacheStateKey($stateId), function () use ($stateId)  {
            return self::where('state_id', $stateId)->where('is_active', true)->orderBy('order')->orderBy('id','DESC')->get();
        });
    }

    public function getState()
    {
        return State::findByField('id', $this->state_id);
    }

    public static function getCacheStateKey($stateId)
    {
        return 'cities_state_'.$stateId;
    }
    
    public function clearCacheTranslate()
    {
        $this->clearCache();
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheStateKey($this->state_id));
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($city) {
            //increase city count of state
            $state = $city->getState();
            $state->update([
                'city_count' => $state->city_count + 1
            ]);

            $city->clearCache();
        });

        static::deleting(function ($city) {
            //increase city count of state
            $state = $city->getState();
            $state->update([
                'city_count' => $state->city_count  - 1
            ]);  
            $city->clearCache();
        });

        static::saved(function ($city) {
            $city->clearCache();
        });
    }
}