<?php
namespace Modules\AppChannelInstagramUnofficial\Facades;

use Illuminate\Support\Facades\Facade;

class IGUnofficial extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'Modules\AppChannelInstagramUnofficial\Services\InstagramUnofficialService';
    }
   
}


