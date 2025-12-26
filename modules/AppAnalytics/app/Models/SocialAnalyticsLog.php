<?php
namespace Modules\AppAnalytics\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAnalyticsLog extends Model
{
    public $timestamps = false;

    protected $table = 'social_analytics_sync_log';

    protected $fillable = [
        'account_id',
        'social_network',
        'type',
        'date',
        'synced_at',
    ];
}