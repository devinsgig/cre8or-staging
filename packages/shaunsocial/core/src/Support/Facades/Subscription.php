<?php


namespace Packages\ShaunSocial\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Subscription extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'subscription';
    }
}
