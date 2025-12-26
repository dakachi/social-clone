<?php
namespace Modules\AppWatermark\Facades;

use Illuminate\Support\Facades\Facade;

class Watermark extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Modules\AppWatermark\Services\WatermarkService';
    }
}