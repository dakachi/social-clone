<?php

namespace Modules\AdminManualPayments\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentManual extends Model
{
    protected $table = 'payment_manual';

    public $timestamps = false;

    protected $dates = [
        'created',
        'changed',
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'uid');
    }

    public function plan()
    {
        return $this->belongsTo(\Modules\AdminPlans\Models\Plans::class, 'plan_id', 'id');
    }
}
