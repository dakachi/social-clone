<?php

namespace Modules\AppRssSchedules\Models;

use Illuminate\Database\Eloquent\Model;

class RssScheduleHistory extends Model
{
    protected $table = 'rss_schedules_history';
    public $timestamps = false;
    protected $guarded = [];
}
