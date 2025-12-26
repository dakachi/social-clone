<?php

namespace Modules\AdminAffiliate\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $table = 'affiliate';

    protected $guarded = [];

    public $timestamps = false;

    public function payment()
    {
        return $this->belongsTo(\Modules\AdminPaymentHistory\Models\PaymentHistory::class, 'payment_id');
    }
}
