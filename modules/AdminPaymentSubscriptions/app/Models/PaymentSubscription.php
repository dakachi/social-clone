<?php

namespace Modules\AdminPaymentSubscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentSubscription extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'uid'            => 'integer',
        'plan_id'        => 'integer',
        'type'           => 'integer',
        'amount'         => 'float',
        'status'         => 'integer',
        'created'        => 'integer',
    ];

    public function getCreated()
    {
        return $this->created ? date('Y-m-d H:i:s', $this->created) : null;
    }

    public function getChanged()
    {
        return $this->changed ? date('Y-m-d H:i:s', $this->changed) : null;
    }

    public static function userHasActive($userId)
    {
        return static::where('uid', $userId)
            ->where('status', 1)
            ->exists();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function plan()
    {
        return $this->belongsTo(Plans::class, 'plan_id', 'id');
    }
}
