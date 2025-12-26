<?php

namespace Modules\AppDashboard\Facades;

use Illuminate\Support\Facades\Facade;

class AppDashboard extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Modules\AppDashboard\Services\AppDashboardService::class;
    }
}