<?php
namespace Modules\AdminMailSender\Facades;

use Illuminate\Support\Facades\Facade;

class MailSender extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Modules\AdminMailSender\Services\MailService';
    }
}