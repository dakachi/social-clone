<?php

namespace Modules\AdminPaymentHistory\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AdminPaymentHistory\Models\PaymentHistory;

class PaymentHistory extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'payment_history';
    }

    public static function totalIncome(Carbon $startDate = null, Carbon $endDate = null)
    {
        $query = PaymentHistory::where('status', 1);

        if ($startDate && $endDate) {
            $query->whereBetween('created', [$startDate->timestamp, $endDate->timestamp]);
        }

        return $query->sum('amount');
    }

    public static function incomeByDay(Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) $startDate = Carbon::now()->subDays(30);
        if (!$endDate) $endDate = Carbon::now();

        $rows = PaymentHistory::where('status', 1)
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->selectRaw('FROM_UNIXTIME(created, "%b %d") as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderByRaw('MIN(created) ASC')
            ->get();

        $categories = [];
        $data = [];

        foreach ($rows as $row) {
            $categories[] = $row->day;
            $data[] = round($row->total, 2);
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => __('Income'),
                    'data' => $data
                ]
            ]
        ];
    }

    public static function latestPayments($limit = 10)
    {
        return \DB::table('payment_history')
            ->leftJoin('users', 'users.id', '=', 'payment_history.uid')
            ->leftJoin('plans', 'plans.id', '=', 'payment_history.plan_id')
            ->where('payment_history.status', 1) 
            ->orderByDesc('payment_history.created')
            ->limit($limit)
            ->select([
                'payment_history.id_secure',
                'payment_history.amount',
                'payment_history.currency',
                'payment_history.created',
                'payment_history.transaction_id',
                'users.fullname as user_fullname',
                'users.avatar as user_avatar',
                'users.email as user_email',
                'plans.name as plan_name'
            ])
            ->get();
    }
}
