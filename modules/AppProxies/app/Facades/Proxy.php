<?php

namespace Modules\AppProxies\Facades;

use Illuminate\Support\Facades\Facade;

class Proxy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Modules\AppProxies\Services\ProxyService';
    }
}