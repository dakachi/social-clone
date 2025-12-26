<?php

namespace Modules\AdminPaymentReport\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Carbon\Carbon;

class PaymentReport extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'PaymentReport';
    }

    public static function paymentInfo(Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) $startDate = Carbon::now()->subDays(30);
        if (!$endDate) $endDate = Carbon::now();

        // Get previous period (same duration as current)
        $prevStart = $startDate->copy()->subDays($endDate->diffInDays($startDate));
        $prevEnd = $startDate;

        // === Total income ===
        $currentIncome = static::totalIncome($startDate, $endDate);
        $previousIncome = static::totalIncome($prevStart, $prevEnd);
        $incomeGrowth = static::calcGrowth($previousIncome, $currentIncome);

        // === Successful transactions ===
        $successTransactions = PaymentHistory::where('status', 1)
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->count();

        $previousSuccess = PaymentHistory::where('status', 1)
            ->whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
            ->count();

        $successGrowth = static::calcGrowth($previousSuccess, $successTransactions);

        // === Refunded transactions ===
        $refundedTransactions = PaymentHistory::where('status', 0)
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->count();

        $previousRefunded = PaymentHistory::where('status', 0)
            ->whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
            ->count();

        $refundGrowth = static::calcGrowth($previousRefunded, $refundedTransactions);

        // === Most used gateway by total amount ===
        $topGateway = static::topGateway($startDate, $endDate);

        // Return all key metrics as an array
        return [
            'total_income' => $currentIncome,
            'income_growth' => $incomeGrowth,

            'success_transactions' => $successTransactions,
            'success_growth' => $successGrowth,

            'refunded_transactions' => $refundedTransactions,
            'refund_growth' => $refundGrowth,

            'top_gateway' => $topGateway,
        ];
    }

    public static function totalIncome(Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) $startDate = Carbon::now()->subDays(30);
        if (!$endDate) $endDate = Carbon::now();

        $query = PaymentHistory::where('status', 1);

        if ($startDate && $endDate) {
            $query->whereBetween('created', [$startDate->timestamp, $endDate->timestamp]);
        }

        return $query->sum('amount');
    }

    public static function paymentByGateway(Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) $startDate = Carbon::now()->subDays(30);
        if (!$endDate) $endDate = Carbon::now();

        $query = PaymentHistory::selectRaw('`from`, SUM(amount) as total')->where('status', 1);

        if ($startDate && $endDate) {
            $query->whereBetween('created', [$startDate->timestamp, $endDate->timestamp]);
        }

        $rows = $query->groupBy('from')
            ->orderByDesc('total')
            ->get();

        $categories = [];
        $data = [];

        foreach ($rows as $row) {
            $categories[] = match($row->from) {
                'paypal' => 'PayPal',
                'stripe' => 'Stripe',
                'manual' => 'Manual Transfer',
                default => ucfirst($row->from ?? 'Unknown'),
            };

            $data[] = round($row->total, 2);
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => __('Total Amount'),
                    'data' => $data
                ]
            ]
        ];
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

    public static function incomeByPlan(Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) $startDate = Carbon::now()->subDays(30);
        if (!$endDate) $endDate = Carbon::now();

        $query = PaymentHistory::query()
            ->selectRaw('plans.name as plan_name, SUM(payment_history.amount) as total')
            ->join('plans', 'plans.id', '=', 'payment_history.plan_id')
            ->where('payment_history.status', 1)
            ->whereBetween('payment_history.created', [$startDate->timestamp, $endDate->timestamp])
            ->groupBy('plan_name')
            ->orderByDesc('total');

        $rows = $query->get();

        $categories = [];
        $data = [];

        foreach ($rows as $row) {
            $categories[] = $row->plan_name ?? 'Unknown';
            $data[] = round($row->total, 2);
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => __('Revenue'),
                    'data' => $data
                ]
            ]
        ];
    }

    public static function topGateway(Carbon $startDate = null, Carbon $endDate = null)
    {

        if (!$startDate) $startDate = Carbon::now()->subDays(30);
        if (!$endDate) $endDate = Carbon::now();

        $query = PaymentHistory::where('status', 1); // Only successful payments

        if ($startDate && $endDate) {
            $query->whereBetween('created', [$startDate->timestamp, $endDate->timestamp]);
        }

        $result = $query->selectRaw('`from`, SUM(amount) as total')
            ->groupBy('from')
            ->orderByDesc('total')
            ->first();

        return match($result->from ?? null) {
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'manual' => 'Manual Transfer',
            default => ucfirst($result->from ?? 'Unknown')
        };
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
                'payment_history.from',
                'payment_history.transaction_id',
                'payment_history.status',
                'users.fullname as user_fullname',
                'users.avatar as user_avatar',
                'users.email as user_email',
                'plans.name as plan_name'
            ])
            ->get();
    }

    /**
     * Calculate percentage growth between two values.
     */
    protected static function calcGrowth($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
