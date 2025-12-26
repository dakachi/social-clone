<?php

namespace Modules\AppRssSchedules\Models;

use Illuminate\Database\Eloquent\Model;

class RssSchedule extends Model
{
    protected $table = 'rss_schedules';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'accounts' => 'array',
        'settings' => 'array',
    ];
}
