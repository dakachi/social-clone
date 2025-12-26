<?php

namespace Modules\AdminPlans\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminPlans\Database\Factories\PlansFactory;

class Plans extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'permissions' => 'array',
    ]; 

    public function subscriptions()
    {
        return $this->hasMany(\Modules\AdminPaymentSubscriptions\Models\PaymentSubscription::class, 'plan_id', 'id');
    }
}
