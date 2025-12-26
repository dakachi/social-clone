<?php

namespace Modules\AdminAffiliate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminAffiliate\Database\Factories\AffiliateWithdrawalFactory;

class AffiliateWithdrawal extends Model
{
    use HasFactory;

    protected $table = 'affiliate_withdrawal';

    protected $guarded = [];

    public $timestamps = false;
}
