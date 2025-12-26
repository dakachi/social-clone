<?php
namespace Modules\AppChannelXUnofficial\Facades;

use Illuminate\Support\Facades\Facade;

class XUnofficial extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'Modules\AppChannelXUnofficial\Services\XUnofficialService';
    }
   
}


