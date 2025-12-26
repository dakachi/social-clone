<?php

namespace Modules\AdminPaymentHistory\Models;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $table = "payment_history";
    protected $guarded = [];
    public $timestamps = false;

    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? 'Paid' : 'Pending';
    }

    public function getCreatedAtFormattedAttribute()
    {
        return \Carbon\Carbon::createFromTimestamp($this->created)->format('d/m/Y');
    }

    public function getPlanNameAttribute()
    {
        if (function_exists('plan_name')) {
            return plan_name($this->plan_id);
        }
        return $this->plan_id;
    }

    public function getAmountFormattedAttribute()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getInvoiceUrlAttribute()
    {
        return route('billing.invoice', $this->id_secure);
    }

    public function plan()
    {
        return $this->belongsTo(\Modules\AdminPlans\Models\Plans::class, 'plan_id', 'id');
    }
}
