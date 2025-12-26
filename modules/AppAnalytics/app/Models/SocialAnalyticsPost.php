<?php

namespace Modules\AppAnalytics\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAnalyticsPost extends Model
{
    protected $table = 'social_analytics_posts';

    protected $fillable = [
        'account_id',
        'social_network',
        'post_id',
        'date',
        'message',
        'created_time',
        'full_picture',
        'permalink_url',
        'type',
        'status_type',
        'details',
        'created',
    ];

    public $timestamps = false;
}