<?php

namespace Modules\AdminCoupons\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AdminCoupons\Database\Factories\CouponsFactory;

class Coupons extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): CouponsFactory
    // {
    //     // return CouponsFactory::new();
    // }
}
