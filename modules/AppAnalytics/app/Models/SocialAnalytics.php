<?php

namespace Modules\AppAnalytics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AppAnalytics\Database\Factories\SocialAnalyticsModelFactory;

class SocialAnalytics extends Model
{
    use HasFactory;

    protected $table = 'social_analytics';

    protected $fillable = [
        'account_id',
        'social_network',
        'metric',
        'value',
        'date',
        'hour',
        'created',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created = $model->created = time();
        });

        static::updating(function ($model) {
            $model->created = time();
        });
    }
}
