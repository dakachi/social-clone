<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Home extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'App\Services\HomeService';
    }
}