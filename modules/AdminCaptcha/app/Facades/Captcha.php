<?php

namespace Modules\AdminCaptcha\Facades;

use Illuminate\Support\Facades\Facade;

class Captcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Modules\AdminCaptcha\Services\CaptchaService::class;
    }
}