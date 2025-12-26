<?php
namespace Modules\AppMediaSearch\Facades;

use Illuminate\Support\Facades\Facade;

class SearchMedia extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'searchmedia';
    }
}