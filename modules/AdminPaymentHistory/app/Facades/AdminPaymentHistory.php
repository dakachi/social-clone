<?php

namespace Modules\AdminPaymentHistory\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;

class AdminPaymentHistory extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'payment_history';
    }

    protected static $table = 'payment_history';

    public static function payment_status($transaction_id)
    { 
        $status = static::payment_info($transaction_id);

        return [
            "status_transaction_id" => $status,
        ];
    }

    public static function payment_info($transaction_id)
    {
        return DB::table(static::$table)
            ->where('transaction_id', $transaction_id)
            ->value('status'); // Lấy trạng thái giao dịch
    }
}
