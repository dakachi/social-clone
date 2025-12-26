<?php

namespace Modules\AppPublishing\Facades;

use Illuminate\Support\Facades\Facade;

class Publishing extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'Modules\AppPublishing\Services\PublishingService';
    }
}


