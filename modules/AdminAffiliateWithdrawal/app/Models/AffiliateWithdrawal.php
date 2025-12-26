<?php

namespace Modules\AdminAffiliateWithdrawal\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawal extends Model
{
    protected $table = 'affiliate_withdrawal'; // Tên bảng trong database

    protected $fillable = [
        'id_secure', 'affiliate_uid', 'amount', 
        'bank', 'notes', 'status', 'created'
    ];

    public $timestamps = false; // Nếu bảng không có created_at & updated_at
}
