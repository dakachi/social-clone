<?php

namespace Modules\AppAnalytics\Facades;

use Illuminate\Support\Facades\Facade;

class Analytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Modules\AppAnalytics\Services\AnalyticsManager';
    }
}