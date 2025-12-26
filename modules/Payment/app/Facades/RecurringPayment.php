<?php

namespace Modules\Payment\Facades;

use Illuminate\Support\Facades\Facade;

class RecurringPayment extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'Modules\Payment\Services\RecurringPaymentService';
    }
}
