<?php

namespace Modules\AdminCredits\Facades;

use Illuminate\Support\Facades\Facade;

class Credit extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return \Modules\AdminCredits\Services\CreditService::Class;
    }
    
}


