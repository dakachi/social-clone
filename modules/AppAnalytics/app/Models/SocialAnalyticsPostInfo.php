<?php

namespace Modules\AppAnalytics\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAnalyticsPostInfo extends Model
{
    protected $table = 'social_analytics_post_infos';

    protected $fillable = [
        'post_id',
        'account_id',
        'social_network',
        'metric',
        'value',
        'date',
        'created',
    ];

    public $timestamps = false;
}