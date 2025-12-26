<?php

namespace Modules\AdminDashboard\Facades;

use Illuminate\Support\Facades\Facade;

class AdminDashboard extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Modules\AdminDashboard\Services\AdminDashboardService::class;
    }
}