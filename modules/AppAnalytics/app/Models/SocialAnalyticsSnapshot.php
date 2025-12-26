<?php
namespace Modules\AppAnalytics\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAnalyticsSnapshot extends Model
{
    protected $table = 'social_analytics_snapshots';

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'social_network',
        'date',
        'data',
        'created',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created = time();
        });

        static::updating(function ($model) {
            $model->created = time();
        });
    }
}
