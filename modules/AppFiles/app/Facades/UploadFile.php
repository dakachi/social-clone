<?php

namespace Modules\AppFiles\Facades;

use Illuminate\Support\Facades\Facade;
use DB;

class UploadFile extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return \Modules\AppFiles\Services\UploadFileService::Class;
    }
    
}


