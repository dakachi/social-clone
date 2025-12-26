<?php

namespace Modules\AdminCredits\Models;

use Illuminate\Database\Eloquent\Model;

class CreditUsage extends Model
{
    protected $table = 'credit_usages';
    
    protected $guarded = [];

    public $timestamps = false;
}