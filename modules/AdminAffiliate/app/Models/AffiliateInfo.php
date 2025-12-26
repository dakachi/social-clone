<?php

namespace Modules\AdminAffiliate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminAffiliate\Database\Factories\AffiliateInfoFactory;

class AffiliateInfo extends Model
{
    use HasFactory;

    protected $table = 'affiliate_info';

    protected $guarded = [];

    public $timestamps = false;
}
