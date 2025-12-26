<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class HeaderManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Services\HeaderManager::class;
    }
}