<?php

namespace Modules\AdminAffiliate\Facades;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;
use Modules\AdminAffiliate\Models\Affiliate as AffiliateModel;
use Modules\AdminAffiliate\Models\AffiliateInfo;
use Modules\AdminAffiliate\Models\AffiliateWithdrawal;
use App\Models\User;

class Affiliate extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'affiliate';
    }

    protected static $table = 'affiliate';
    protected static $table_info = 'affiliate_info';

    protected static function info($name = false, $uid = false, $formatted = true)
    {
        $report = static::report($uid, $formatted);

        if ($formatted) {
            $info = [
                "min_withdrawal" => get_option("affiliate_minimum_withdrawal", ""),
                "currency_symbol" => "$",
                "total_count" => \Number::format($report['total_count']),
                "total_amount" => \Core::currency(($report['total_amount'])),
                "total_commission" => \Core::currency(($report['total_commission'])),
                "total_clicks" => \Number::format($report['total_clicks']),
                "total_withdrawal" => \Core::currency(($report['total_withdrawal'])),
                "total_conversions" => \Number::format($report['total_conversions']),
                "total_approved" => \Core::currency(($report['total_approved'])),
                "total_balance" => \Core::currency(($report['total_balance'])),
                "status_counts" => $report['status_counts'],
                "pending_count" => \Number::format($report['status_counts']['pending']['count']),
                "pending_amount" => \Core::currency(($report['status_counts']['pending']['amount'])),
                "pending_commission" => \Core::currency(($report['status_counts']['pending']['commission'])),
                "approved_count" => \Number::format($report['status_counts']['approved']['count']),
                "approved_amount" => \Core::currency(($report['status_counts']['approved']['amount'])),
                "approved_commission" => \Core::currency(($report['status_counts']['approved']['commission'])),
                "rejected_count" => \Number::format($report['status_counts']['rejected']['count']),
                "rejected_amount" => \Core::currency(($report['status_counts']['rejected']['amount'])),
                "rejected_commission" => \Core::currency(($report['status_counts']['rejected']['commission'])),
            ];
        }else{
            $info = [
                "min_withdrawal" => get_option("affiliate_minimum_withdrawal", ""),
                "currency_symbol" => "$",
                "total_count" => $report['total_count'],
                "total_amount" => $report['total_amount'],
                "total_commission" => $report['total_commission'],
                "total_clicks" => $report['total_clicks'],
                "total_withdrawal" => $report['total_withdrawal'],
                "total_conversions" => $report['total_conversions'],
                "total_approved" => $report['total_approved'],
                "total_balance" => $report['total_balance'],
                "status_counts" => $report['status_counts'],
                "pending_count" => $report['status_counts']['pending']['count'],
                "pending_amount" => $report['status_counts']['pending']['amount'],
                "pending_commission" => $report['status_counts']['pending']['commission'],
                "approved_count" => $report['status_counts']['approved']['count'],
                "approved_amount" => $report['status_counts']['approved']['amount'],
                "approved_commission" => $report['status_counts']['approved']['commission'],
                "rejected_count" => $report['status_counts']['rejected']['count'],
                "rejected_amount" => $report['status_counts']['rejected']['amount'],
                "rejected_commission" => $report['status_counts']['rejected']['commission'],
            ];
        }

        if ($name) {
            if (isset($info[$name])) {
                return $info[$name];
            }

            return null;
        }

        return $info;
    }

    public static function report($uid = false, $formatted = true)
    {
        $affiliateQuery = AffiliateModel::query();
        if ($uid != false) {
            $affiliateQuery->where('affiliate_uid', $uid);
        }

        $affiliateTotals = $affiliateQuery
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(amount) as total_amount,
                SUM(commission) as total_commission
            ')
            ->first();

        $affiliateQuery = AffiliateModel::query();
        if ($uid != false) {
            $affiliateQuery->where('affiliate_uid', $uid);
        }

        $statusCounts = $affiliateQuery
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                SUM(commission) as total_commission
            ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $affiliateInfoQuery = AffiliateInfo::query();
        if ($uid != false) {
            $affiliateInfoQuery->where('affiliate_uid', $uid); 
        }

        $affiliateInfoTotals = $affiliateInfoQuery
            ->selectRaw('
                SUM(clicks) as total_clicks,
                SUM(total_withdrawal) as total_withdrawal,
                SUM(conversions) as total_conversions,
                SUM(total_approved) as total_approved,
                SUM(total_balance) as total_balance
            ')
            ->first();

        return [
            "total_count" => $affiliateTotals->total_count ?? 0,
            "total_amount" => $affiliateTotals->total_amount ?? 0,
            "total_commission" => $affiliateTotals->total_commission ?? 0,

            "total_clicks" => $affiliateInfoTotals->total_clicks ?? 0,
            "total_withdrawal" => $affiliateInfoTotals->total_withdrawal ?? 0,
            "total_conversions" => $affiliateInfoTotals->total_conversions ?? 0,
            "total_approved" => $affiliateInfoTotals->total_approved ?? 0,
            "total_balance" => $affiliateInfoTotals->total_balance ?? 0,

            'status_counts' => [
                "approved" => [
                    "count" => $statusCounts[1]->count ?? 0,
                    "amount" => $statusCounts[1]->total_amount ?? 0,
                    "commission" => $statusCounts[1]->total_commission ?? 0,
                ],
                "rejected" => [
                    "count" => $statusCounts[2]->count ?? 0,
                    "amount" => $statusCounts[2]->total_amount ?? 0,
                    "commission" => $statusCounts[2]->total_commission ?? 0,
                ],
                "pending" => [
                    "count" => $statusCounts[0]->count ?? 0,
                    "amount" => $statusCounts[0]->total_amount ?? 0,
                    "commission" => $statusCounts[0]->total_commission ?? 0,
                ],
            ],
        ];
    }

    public static function reportCommissionByMonth($uid = false, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subMonths(12);
        }
        if (!$endDate) {
            $endDate = Carbon::now();
        }

        $query = AffiliateModel::query();

        if ($uid != false) {
            $query->where('affiliate_uid', $uid);
        }

        $query->whereBetween('created', [$startDate->timestamp, $endDate->timestamp]);

        $monthlyData = $query->selectRaw('FROM_UNIXTIME(created, "%Y-%m") as month, SUM(commission) as total_commission')
            ->groupBy('month')
            ->orderByRaw("MIN(created) ASC")
            ->get();

        $categories = [];
        $data = [];

        foreach ($monthlyData as $row) {
            $categories[] = $row->month;
            $data[] = round($row->total_commission, 2);
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => __('Commission'),
                    'data' => $data
                ]
            ]
        ];
    }

    public static function reportCommissionByDay($uid = false, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subDays(30);
        }
        if (!$endDate) {
            $endDate = Carbon::now();
        }

        $query = AffiliateModel::query();

        if ($uid != false) {
            $query->where('affiliate_uid', $uid);
        }

        $query->whereBetween('created', [$startDate->timestamp, $endDate->timestamp]);

        $dailyData = $query->selectRaw('FROM_UNIXTIME(created, "%b %d") as day, SUM(commission) as total_commission')
            ->groupBy('day')
            ->orderByRaw("MIN(created) ASC")
            ->get();

        $categories = [];
        $data = [];

        foreach ($dailyData as $row) {
            $categories[] = $row->day;
            $data[] = round($row->total_commission, 2);
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => __('Commission'),
                    'data' => $data
                ]
            ]
        ];
    }

    public static function reportWithdrawalByDay($uid = false, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) {
            $startDate = Carbon::now()->subDays(30);
        }
        if (!$endDate) {
            $endDate = Carbon::now();
        }

        $query = AffiliateWithdrawal::query();
        if ($uid != false) {
            $query->where('affiliate_uid', $uid);
        }

        $rows = $query->selectRaw('FROM_UNIXTIME(created, "%b %d, %Y") as date, status, SUM(amount) as total_amount')
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->groupBy('date', 'status')
            ->orderBy('date', 'asc')
            ->get();

        // Pivot as before
        $pivot = [];
        foreach ($rows as $row) {
            $pivot[$row->date][$row->status] = $row->total_amount;
        }

        $allDates = [];
        $date = $startDate->copy();
        while ($date->lte($endDate)) {
            $allDates[] = $date->format('M d, Y');
            $date->addDay();
        }

        $statuses = [0, 1, 2];
        $colors = ['#c0c8d2', '#675dff', '#ff4c4c'];
        $statusNames = [0 => __('Pending'), 1 => __('Approved'), 2 => __('Rejected')];

        $series = [];
        foreach ($statuses as $status) {
            $data = [];
            foreach ($allDates as $date) {
                $data[] = isset($pivot[$date][$status]) ? (float)$pivot[$date][$status] : 0;
            }
            $series[] = [
                'name'  => $statusNames[$status],
                'data'  => $data,
                'color' => $colors[$status]
            ];
        }

        return [
            'categories' => $allDates,
            'series' => $series
        ];
    }

    public static function updateStatus(array $affiliate_ids, int $status)
    {
        if (empty($affiliate_ids)) {
            return [
                "status"  => 0,
                "message" => __("Please select at least one item"),
            ];
        }

        $affiliates = AffiliateModel::whereIn('id_secure', $affiliate_ids)->get();
        if($affiliates){
            foreach ($affiliates as $key => $value) {

                $affiliate_info = AffiliateInfo::where("affiliate_uid", $value->affiliate_uid)->first();

                if(!$affiliate_info){
                    $affiliate_info = AffiliateInfo::create([
                        "id_secure" => rand_string(),
                        "affiliate_uid" => $value->affiliate_uid,
                        "clicks" => 0,
                        "conversions" => 0,
                        "total_approved" => 0,
                        "total_balance" => 0,

                    ]);
                }

                $affiliate_id = $affiliate_info->id;

                $total_approved =  $affiliate_info->total_approved;
                $total_balance =  $affiliate_info->total_balance;
                $amount =  $value->commission;

                if((int)$value->status !== (int)$status){
                    switch ($status) {
                        case 1:
                            $total_approved += $amount;
                            $total_balance += $amount;
                            break;

                        case 2:
                            if($value->status == 1){
                                $total_approved -= $amount;
                                $total_balance -= $amount;
                            }
                            break;
                        
                        default:
                            if($value->status == 1){
                                $total_approved -= $amount;
                                $total_balance -= $amount;
                            }
                            break;
                    }

                    $params = [
                        "total_approved" => $total_approved > 0?$total_approved:0,
                        "total_balance" => $total_balance > 0?$total_balance:0,
                    ];

                    AffiliateInfo::where("id", $affiliate_info->id)->update($params);
                    AffiliateModel::where('id', $value->id)->update(['status' => $status]);
                }
            }
        }

        return [
            'status' => 1,
            'message' => 'Succeed'
        ];
    }

    public static function updateWithdrawalStatus(array $request_withdrawal_ids, int $status)
    {
        if (empty($request_withdrawal_ids)) {
            return [
                "status"  => 0,
                "message" => __("Please select at least one item"),
            ];
        }

        $requestWithdrawals = AffiliateWithdrawal::whereIn('id_secure', $request_withdrawal_ids)->get();

        if($requestWithdrawals){
            foreach ($requestWithdrawals as $key => $value) {

                $affiliate_info = AffiliateInfo::where("affiliate_uid", $value->affiliate_uid)->first();

                if(!$affiliate_info){
                    return [
                        "status"  => 0,
                        "message" => __("Unable to process the withdrawal request due to insufficient account balance."),
                    ];
                }

                $minimum_withdrawal = get_option("affiliate_minimum_withdrawal", "50");
                $affiliate_id = $affiliate_info->id;
                $total_approved =  $affiliate_info->total_approved;
                $total_balance =  $affiliate_info->total_balance;
                $total_withdrawal = $affiliate_info->total_withdrawal;
                $amount =  $value->amount;

                /*if($total_balance < $amount && $status == 1){
                    return [
                        "status"  => 0,
                        "message" => __("Unable to process the withdrawal request due to insufficient account balance."),
                    ];
                }

                if($minimum_withdrawal > $amount && $status == 1){
                    return [
                        "status"  => 0,
                        "message" => __("Unable to process the withdrawal request because the amount is less than the affiliate's minimum withdrawal threshold."),
                    ];
                }*/

                if((int)$value->status !== (int)$status){
                    switch ($status) {
                        case 1:
                            if($value->status == 0){
                                $total_withdrawal += $amount;
                            }

                            if($value->status == 2){
                                $total_balance -= $amount;
                                $total_withdrawal += $amount;
                            }
                            break;

                        case 2:
                            if($value->status == 1){
                                $total_withdrawal -= $amount;
                                $total_balance += $amount;
                            }

                            if($value->status == 0){
                                $total_balance += $amount;
                            }
                            break;
                        
                        default:
                            if($value->status == 1){
                                $total_withdrawal -= $amount;
                            }

                            if($value->status == 2){
                                $total_balance -= $amount;
                            }
                            break;
                    }


                    $params = [
                        "total_withdrawal" => $total_withdrawal > 0?$total_withdrawal:0,
                        "total_balance" => $total_balance > 0?$total_balance:0,
                    ];

                    AffiliateInfo::where("id", $affiliate_info->id)->update($params);
                    AffiliateWithdrawal::where('id', $value->id)->update(['status' => $status]);
                }
            }
        }

        return [
            'status' => 1,
            'message' => 'Succeed'
        ];
    }

    /**
     * Apply pending commission for an affiliate user.
     * Create a new affiliate record with status 0 (pending).
     *
     * @param int $userId          Affiliate user id.
     * @param float $amount        Payment amount.
     * @param int|null $paymentId  (Optional) Payment ID reference.
     * @param float|null $rate     (Optional) Commission percent, default from config.
     * @return bool
     */
    public static function applyCommission(int $userId, float $amount, ?int $paymentId = null, ?float $rate = null): bool
    {
        if( session()->has("ref") )
        {
            $refCode = session("ref");
            $affiliateUserId = User::where('id_secure', $refCode)->value('id');
            if($affiliateUserId && $affiliateUserId != $userId && $amount > 0){
                // Get commission rate, default to 15%
                $commissionRate = $rate ?? get_option("affiliate_commission_percentage", 15);

                // Calculate commission amount
                $commission = round($amount * $commissionRate / 100, 2);

                try {
                    AffiliateModel::create([
                        'id_secure'       => rand_string(),
                        'affiliate_uid'   => $affiliateUserId,
                        'payment_id'      => $paymentId,
                        'amount'          => $amount,
                        'commission_rate' => $commissionRate,
                        'commission'      => $commission,
                        'status'          => 0, 
                        'created'         => time(),
                    ]);
                    return true;
                } catch (\Throwable $e) {
                    \Log::error('Failed to apply commission', [
                        'user_id'         => $affiliateUserId,
                        'amount'          => $amount,
                        'payment_id'      => $paymentId,
                        'commission_rate' => $commissionRate,
                        'commission'      => $commission,
                        'error'           => $e->getMessage(),
                    ]);
                    return false;
                }

            }else{
                return false;
            }
        }

        return false;
    }
}


