<?php

namespace Modules\AdminNotifications\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'source', 'mid', 'type', 'message', 'url', 'read_at'
    ];

    public function manual()
    {
        return $this->belongsTo(NotificationManual::class, 'mid');
    }
}