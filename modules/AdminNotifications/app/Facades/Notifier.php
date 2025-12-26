<?php

namespace Modules\AdminNotifications\Facades;

use Illuminate\Support\Facades\Facade;

class Notifier extends Facade
{
    protected static function getFacadeAccessor()
    {
        return '\Modules\AdminNotifications\Services\NotificationService';
    }
}