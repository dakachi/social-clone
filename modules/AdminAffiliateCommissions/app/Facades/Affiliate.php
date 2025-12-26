<?php

namespace Modules\AdminAffiliate\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AdminAffiliate\Models\Affiliate as AffiliateModel;
use Modules\AdminAffiliate\Models\AffiliateInfo;
use DB;

class Affiliate extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'affiliate';
    }

    protected static $table = 'affiliate';

    protected static function info($name = false, $uid = false)
    { 
        $report = static::report($uid);

        $info = [
            "min_withdrawal" => get_option("affiliate_minimum_withdrawal", ""),
            "currency_symbol" => "$",
            "total_count" => $report['total_count'],
            "total_amount" => $report['total_amount'],
            "total_commission" => $report['total_commission'],
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

        if($name){
            if(isset($info[$name])){
                return $info[$name];
            }

            return null;
        }

        return $info;
        
    }

    public static function report($uid = false)
    {
        $table = static::$table;

        // Build a base query for both totals and status-based queries
        $query = DB::table($table);
        
        // If $uid is provided (not false), filter by affiliate_uid
        if ($uid !== false) {
            $query->where('affiliate_uid', $uid);
        }

        // Get the total count, total amount, and total commission
        $totals = (clone $query)
            ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_amount, SUM(commission) as total_commission')
            ->first();

        // Get data grouped by status
        $statusData = (clone $query)
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount, SUM(commission) as total_commission')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Return the data as a JSON response
        return [
            "total_count" => $totals->total_count ?? 0,
            "total_amount" => $totals->total_amount ?? 0,
            "total_commission" => $totals->total_commission ?? 0,
            "status_counts" => [
                "approved" => [
                    "count" => $statusData[1]->count ?? 0,
                    "amount" => $statusData[1]->total_amount ?? 0,
                    "commission" => $statusData[1]->total_commission ?? 0,
                ],
                "rejected" => [
                    "count" => $statusData[2]->count ?? 0,
                    "amount" => $statusData[2]->total_amount ?? 0,
                    "commission" => $statusData[2]->total_commission ?? 0,
                ],
                "pending" => [
                    "count" => $statusData[0]->count ?? 0,
                    "amount" => $statusData[0]->total_amount ?? 0,
                    "commission" => $statusData[0]->total_commission ?? 0,
                ],
            ]
        ];
    }

    public static function updateStatus(array $affiliate_ids, int $status){

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

        return true;
    }
}


