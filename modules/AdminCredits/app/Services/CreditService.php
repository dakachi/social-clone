<?php

namespace Modules\AdminCredits\Services;

use Modules\AdminCredits\Models\CreditUsage;
use Carbon\Carbon;

class CreditService
{
    public function checkQuota($teamId = null)
    {
        $teamId = $this->resolveTeamId($teamId);
        $limit = \UserInfo::getTeamPermission('credits', 0, $teamId);

        if ($limit == -1 || $limit === '-1') {
            return [
                'can_use' => true,
                'limit'   => -1,
                'used'    => 0,
                'left'    => -1,
                'message' => __("Your team has unlimited credits for this plan."),
            ];
        }

        $quotaResetAt = \UserInfo::getDataTeam('quota_reset_at', null, $teamId);
        $nextQuotaResetAt = \UserInfo::getDataTeam('next_quota_reset_at', null, $teamId);

        $startTimestamp = $quotaResetAt;
        $endTimestamp = $nextQuotaResetAt;

        $used = CreditUsage::where('team_id', $teamId)
            ->whereBetween('date', [$startTimestamp, $endTimestamp])
            ->sum('credits_used');

        $left = max(0, intval($limit) - $used);

        return [
            'can_use' => $used < $limit,
            'limit'   => intval($limit),
            'used'    => $used,
            'left'    => $left,
            'message' => $used < $limit
                ? __("Your team has :count credits left in this quota period.", ['count' => $left])
                : __("Your team has reached its credits quota. Please upgrade your plan or wait for the next period."),
        ];
    }

    public function convertToCredits($model, $tokens)
    {
        $rates = json_decode(get_option('credit_rates', '{}'), true);
        $rate = $rates[$model] ?? 1;
        return (int) ceil($tokens * $rate);
    }

    public function getCreditUsageSummary($teamId = 0): array
    {
        $teamId = $this->resolveTeamId($teamId);

        $limit = \UserInfo::getTeamPermission('credits', 0, $teamId);
        $quotaResetAt = \UserInfo::getDataTeam('quota_reset_at', null, $teamId);
        $nextQuotaResetAt = \UserInfo::getDataTeam('next_quota_reset_at', null, $teamId);

        $startTimestamp = $quotaResetAt;
        $endTimestamp = $nextQuotaResetAt;

        $used = CreditUsage::where('team_id', $teamId)
            ->whereBetween('date', [$startTimestamp, $endTimestamp])
            ->sum('credits_used');

        $isUnlimited = ($limit == -1 || $limit === '-1');
        $remaining   = $isUnlimited ? -1 : max(0, intval($limit) - $used);
        $percent     = ($isUnlimited || $limit == 0) ? 0 : min(100, round($used / $limit * 100));
        $quotaReached = !$isUnlimited && $used >= $limit;

        if ($isUnlimited) {
            $percentLabel = __('Unlimited');
            $progressValue = 100;
        } elseif ($quotaReached) {
            $percentLabel = '100%';
            $progressValue = 100;
        } else {
            $percentLabel = $percent . '%';
            $progressValue = $percent;
        }

        return [
            'limit'         => $isUnlimited ? -1 : intval($limit),
            'used'          => $used,
            'remaining'     => $remaining,
            'percent'       => $percent,
            'is_unlimited'  => $isUnlimited,
            'quota_reached' => $quotaReached,
            'progress_label'=> $percentLabel,
            'progress_value'=> $progressValue,
            'message'       => $quotaReached
                ? __("Your team has reached its credits quota. Please upgrade your plan or wait for the next period.")
                : ($isUnlimited
                    ? __("Your team has unlimited credits for this plan.")
                    : __("Your team has :count credits left in this quota period.", ['count' => $remaining])),
        ];
    }

    public function trackUsage(int $credits, $feature = 'ai', string $model, $teamId = 0): void
    {
        $teamId = $this->resolveTeamId($teamId);
        if (is_null($teamId)) return;

        $today = Carbon::now()->startOfDay()->timestamp;

        // Thử update trước
        $updated = \DB::table('credit_usages')
            ->where('team_id', $teamId)
            ->where('feature', $feature)
            ->where('model', $model)
            ->where('date', $today)
            ->increment('credits_used', $credits, ['changed' => time()]);

        // Nếu chưa có row nào thì insert
        if (!$updated) {
            \DB::table('credit_usages')->insert([
                'team_id'      => $teamId,
                'feature'      => $feature,
                'model'        => $model,
                'date'         => $today,
                'credits_used' => $credits,
                'changed'      => time(),
                'created'      => time(),
            ]);
        }
    }

    public function getCreditUsageByModel($teamId = 0, $startDate = null, $endDate = null, $feature = 'ai_%')
    {
        $teamId = $this->resolveTeamId($teamId);
        if (!$startDate) $startDate = now()->subDays(30)->startOfDay();
        if (!$endDate) $endDate = now()->endOfDay();

        $query = CreditUsage::query()
            ->selectRaw('model, SUM(credits_used) as total')
            ->whereBetween('date', [$startDate->timestamp, $endDate->timestamp]);
        
        if ($teamId > 0) $query->where('team_id', $teamId);

        // Thêm lọc theo feature (LIKE ai_%)
        if ($feature) {
            if (str_ends_with($feature, '%')) {
                $query->where('feature', 'like', $feature);
            } else {
                $query->where('feature', $feature);
            }
        }

        $rows = $query->groupBy('model')->get();
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'name' => ucfirst($row->model ?? __('Unknown')),
                'y'    => (int) $row->total,
            ];
        }
        return [
            'series' => [[
                'name' => __('Credits Used'),
                'colorByPoint' => true,
                'data' => $data
            ]]
        ];
    }

    public function getCreditUsageChartData($teamId = 0, $startDate = null, $endDate = null, $feature = 'ai_%')
    {
        $teamId = $this->resolveTeamId($teamId);
        if (!$startDate) $startDate = now()->subDays(30)->startOfDay();
        if (!$endDate) $endDate = now()->endOfDay();

        $query = CreditUsage::query()
            ->selectRaw('FROM_UNIXTIME(date, "%Y-%m-%d") as day, SUM(credits_used) as total')
            ->whereBetween('date', [$startDate->timestamp, $endDate->timestamp]);

        if ($teamId > 0) $query->where('team_id', $teamId);

        if ($feature) {
            if (str_ends_with($feature, '%')) {
                $query->where('feature', 'like', $feature);
            } else {
                $query->where('feature', $feature);
            }
        }

        $usageData = $query->groupBy('day')->pluck('total', 'day')->toArray();

        $categories = [];
        $values = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $categories[] = $date->format('M d');
            $values[] = (int)($usageData[$key] ?? 0);
        }
        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => __('Credits Used'),
                    'data' => $values
                ]
            ]
        ];
    }

    protected function resolveTeamId(int|null $teamId = 0): int|null
    {
        if ($teamId === 0 || $teamId === null) {
            $teamId = (int) request()?->input('team_id', 0) ?? 0;
        }
        if ($teamId === -1) {
            return -1;
        }
        return $teamId;
    }

    public function addCreditRates($module_name, $creditRate)
    {
        $module = \Module::find($module_name);
        $menu = $module->get('menu');

        if ($menu) {
            $creditRate = array_merge($creditRate, [
                'uri'         => $menu['uri'],
                'icon'        => $menu['icon'],
                'color'       => $menu['color'],
                'name'        => $menu['name'],
                'position'    => $menu['position'] ?? 0,
                'id'          => $module->getName(),
                'key'         => $module->getLowerName(),
                'module_name' => $menu['name'],
            ]);

            $creditRates = app()->bound('creditRates') ? app('creditRates') : [];
            $creditRates[] = $creditRate;
            app()->instance('creditRates', $creditRates);
        }
    }
}
