<?php
namespace Modules\AppRssSchedules\Facades;

use Illuminate\Support\Facades\Facade;

class RssAutomation extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'Modules\AppRssSchedules\Services\RssService';
    }
   
}


