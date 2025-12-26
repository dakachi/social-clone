<?php

namespace Modules\AppPublishing\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppPublishing\Models\Posts;
use Modules\AppChannels\Models\Accounts;
use Modules\AppPublishing\Models\PostStat;
use Carbon\Carbon;

class PublishingReport extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'PublishingReport';
    }

    public static function postStatsByDay(Carbon $startDate, Carbon $endDate, $teamId = null)
    {
        $allDays = collect();
        $cur = $startDate->copy();
        while ($cur->lte($endDate)) {
            $allDays->push($cur->format('Y-m-d'));
            $cur->addDay();
        }

        $successQuery = PostStat::query()
            ->selectRaw('FROM_UNIXTIME(created, "%Y-%m-%d") as date, COUNT(*) as total')
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->where('status', 4);
        if ($teamId) $successQuery->where('team_id', $teamId);
        $successData = $successQuery->groupBy('date')->pluck('total', 'date')->toArray();

        $failQuery = PostStat::query()
            ->selectRaw('FROM_UNIXTIME(created, "%Y-%m-%d") as date, COUNT(*) as total')
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->where('status', 5);
        if ($teamId) $failQuery->where('team_id', $teamId);
        $failData = $failQuery->groupBy('date')->pluck('total', 'date')->toArray();

        $successValues = [];
        $failValues = [];
        foreach ($allDays as $day) {
            $successValues[] = (int)($successData[$day] ?? 0);
            $failValues[] = (int)($failData[$day] ?? 0);
        }

        $successTotal = array_sum($successValues);
        $failTotal = array_sum($failValues);
        $total = $successTotal + $failTotal;
        $successRate = $total > 0 ? round($successTotal / $total * 100, 1) : 0;

        return [
            'categories' => $allDays->toArray(),
            'series' => [
                ['name' => __('Success'), 'data' => $successValues],
                ['name' => __('Failed'),  'data' => $failValues],
            ],
            'summary' => [
                'success_total' => $successTotal,
                'fail_total' => $failTotal,
                'total' => $total,
                'success_rate' => $successRate,
            ]
        ];
    }

    public static function postsByTeamForChart(Carbon $startDate, Carbon $endDate)
    {
        $data = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->select('team_id', \DB::raw('COUNT(*) as total'))
            ->groupBy('team_id')
            // ->with('team') // Nếu có relation team
            ->get();

        $categories = [];
        $values = [];
        foreach ($data as $item) {
            $categories[] = $item->team->name ?? ('Team ' . $item->team_id);
            $values[] = (int)$item->total;
        }
        return [
            'categories' => $categories,
            'series' => [
                ['name' => __('Posts'), 'data' => $values]
            ]
        ];
    }

    public static function postsBySocialForChart(Carbon $startDate, Carbon $endDate)
    {
        $data = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->select('social_network', \DB::raw('COUNT(*) as total'))
            ->groupBy('social_network')
            ->get();

        $seriesData = [];
        foreach ($data as $item) {
            $seriesData[] = ['name' => ucfirst($item->social_network), 'y' => (int)$item->total];
        }
        return [
            'series' => [[
                'name' => __('Posts'),
                'data' => $seriesData
            ]]
        ];
    }

    public static function statusMap($key = null)
    {
        $statuses = [
            1 => ['label' => __('Draft'),            'color' => '#f5c542'],
            2 => ['label' => __('Waiting Approve'),  'color' => '#4a90e2'],
            3 => ['label' => __('Processing'),       'color' => '#50e3c2'],
            4 => ['label' => __('Failed'),           'color' => '#ff4d4f'],
            5 => ['label' => __('Success'),          'color' => '#52c41a'],
            6 => ['label' => __('Stop/Pause'),       'color' => '#888888'],
        ];
        return $key ? ($statuses[$key] ?? ['label' => __('Unknown'), 'color' => '#bbb']) : $statuses;
    }

    public static function postInfo(Carbon $startDate, Carbon $endDate, $teamId = null)
    {
        $totalPosts = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->count();

        $rangeDays = $startDate->diffInDays($endDate);
        $prevStart = $startDate->copy()->subDays($rangeDays);
        $prevEnd = $startDate;
        $prevTotal = Posts::whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->count();
        $totalGrowth = static::calcGrowth($prevTotal, $totalPosts);

        $statusMap = static::statusMap();
        $statusCounts = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->select('status', \DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->toArray();

        $statusGrowth = [];
        foreach ($statusMap as $code => $info) {
            $current = (int)($statusCounts[$code] ?? 0);
            $prev = Posts::whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->where('status', $code)
                ->count();
            $statusGrowth[$code] = static::calcGrowth($prev, $current);
        }

        $topAccount = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->select('account_id', \DB::raw('COUNT(*) as total'))
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->with('account')
            ->first();

        $socialDistribution = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->select('social_network', \DB::raw('COUNT(*) as total'))
            ->groupBy('social_network')
            ->pluck('total', 'social_network')->toArray();

        return [
            'total_posts'         => $totalPosts,
            'total_growth'        => $totalGrowth,
            'status_map'          => $statusMap,
            'status_counts'       => $statusCounts,
            'status_growth'       => $statusGrowth,
            'top_account'         => $topAccount,
            'social_distribution' => $socialDistribution,
        ];
    }

    public static function postStatsGrowthInfo(Carbon $startDate, Carbon $endDate, $teamId = null)
    {
        $statusMap = [
            5 => ['label' => __('Failed'),  'color' => '#ff4d4f'],
            4 => ['label' => __('Success'), 'color' => '#52c41a'],
        ];

        $totalPosts = PostStat::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereIn('status', [4, 5])
            ->count();

        $rangeDays = $startDate->diffInDays($endDate);
        $prevStart = $startDate->copy()->subDays($rangeDays);
        $prevEnd = $startDate;

        $prevTotal = PostStat::whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereIn('status', [4, 5])
            ->count();

        $totalGrowth = static::calcGrowth($prevTotal, $totalPosts);

        $statusCounts = PostStat::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereIn('status', [4, 5])
            ->select('status', \DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->toArray();

        $statusGrowth = [];
        foreach ([4, 5] as $code) {
            $current = (int)($statusCounts[$code] ?? 0);
            $prev = PostStat::whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->where('status', $code)
                ->count();
            $statusGrowth[$code] = static::calcGrowth($prev, $current);
        }

        return [
            'total_posts'   => $totalPosts,
            'total_growth'  => $totalGrowth,
            'status_map'    => $statusMap,
            'status_counts' => $statusCounts,
            'status_growth' => $statusGrowth,
        ];
    }

    public static function postsByStatusForChart(Carbon $startDate, Carbon $endDate, $teamId = null)
    {
        $statusMap = static::statusMap();

        $query = Posts::query()
            ->select('status', \DB::raw('COUNT(*) as total'))
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp]);
        if ($teamId) $query->where('team_id', $teamId);

        $statusData = $query->groupBy('status')->pluck('total', 'status')->toArray();

        $categories = [];
        $values = [];
        foreach ($statusMap as $code => $info) {
            $categories[] = $info['label'];
            $values[] = (int)($statusData[$code] ?? 0);
        }

        return [
            'categories' => $categories,
            'series' => [
                ['name' => __('Posts'), 'data' => $values]
            ],
            'summary' => [
                'total' => array_sum($values)
            ]
        ];
    }

    public static function recentPostsStatus($limit = 10, $teamId = null)
    {
        $query = Posts::query()
            ->whereIn('status', [4, 5])
            ->orderByDesc('created');

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $posts = $query->limit($limit)->get();

        $statusMap = static::statusMap();
        foreach ($posts as $post) {
            $post->status_label = $statusMap[$post->status]['label'] ?? $post->status;
            $post->status_color = $statusMap[$post->status]['color'] ?? '#bbb';
        }

        return $posts;
    }

    public static function calcGrowth($previous, $current)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / max(1, $previous)) * 100, 1);
    }
}
