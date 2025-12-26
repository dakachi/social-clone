<?php

namespace Modules\AdminPlans\Facades;

use Illuminate\Support\Facades\Facade;

class Pricing extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Modules\AdminPlans\Services\PricingService::class;
    }
}