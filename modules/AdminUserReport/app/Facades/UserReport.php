<?php
namespace Modules\AdminUserReport\Facades;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;
use App\Models\User;

class UserReport extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'user_report';
    }

    public static function summary()
    {
        $now = Carbon::now();
        $lastWeek = $now->copy()->subWeek();
        $lastMonth = $now->copy()->subMonth();

        // Get user stats from the previous week
        $weekStatsBefore = static::countUsersByStatus(
            $lastWeek->copy()->subWeek()->timestamp,
            $lastWeek->timestamp
        );

        // Get user stats from the current week
        $weekStatsNow = static::countUsersByStatus(
            $lastWeek->timestamp,
            $now->timestamp
        );

        // Get user stats from the previous month
        $monthStatsBefore = static::countUsersByStatus(
            $lastMonth->copy()->subMonth()->timestamp,
            $lastMonth->timestamp
        );

        // Get user stats from the current month
        $monthStatsNow = static::countUsersByStatus(
            $lastMonth->timestamp,
            $now->timestamp
        );

        return [
            // Current total user counts
            'total'    => User::count(),
            'active'   => User::where('status', 2)->count(),
            'inactive' => User::where('status', 1)->count(),
            'banned'   => User::where('status', 0)->count(),

            // Weekly growth comparison
            'weekly_growth' => [
                'total'    => static::calcGrowth($weekStatsBefore['total'], $weekStatsNow['total']),
                'active'   => static::calcGrowth($weekStatsBefore['active'], $weekStatsNow['active']),
                'inactive' => static::calcGrowth($weekStatsBefore['inactive'], $weekStatsNow['inactive']),
                'banned'   => static::calcGrowth($weekStatsBefore['banned'], $weekStatsNow['banned']),
            ],

            // Monthly growth comparison
            'monthly_growth' => [
                'total'    => static::calcGrowth($monthStatsBefore['total'], $monthStatsNow['total']),
                'active'   => static::calcGrowth($monthStatsBefore['active'], $monthStatsNow['active']),
                'inactive' => static::calcGrowth($monthStatsBefore['inactive'], $monthStatsNow['inactive']),
                'banned'   => static::calcGrowth($monthStatsBefore['banned'], $monthStatsNow['banned']),
            ],
        ];
    }

    public static function latestUsers($limit = 10)
    {
        return User::orderBy('created', 'desc')
            ->limit($limit)
            ->get(['id_secure', 'fullname', 'avatar', 'email', 'login_type', 'status', 'created']);
    }

    public static function loginTypeStats()
    {
        $direct = __('Direct');
        return User::selectRaw('login_type, COUNT(*) as count')
            ->groupBy('login_type')
            ->orderByDesc('count')
            ->get()
            ->map(function ($row) {
                return [
                    'name' => ucfirst(__($row->login_type)) ?: __('Unknown'),
                    'y' => (int) $row->count
                ];
            })
            ->values()
            ->toArray();
    }

    public static function dailyRegistrations(Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!$startDate) $startDate = Carbon::now()->subDays(30);
        if (!$endDate) $endDate = Carbon::now();

        $query = User::query()
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp]);

        $data = $query->selectRaw('FROM_UNIXTIME(created, "%b %d") as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderByRaw("MIN(created) ASC")
            ->get()
            ->keyBy('day');

        $allDays = [];
        $cur = $startDate->copy();
        while ($cur->lte($endDate)) {
            $allDays[] = $cur->format('M d');
            $cur->addDay();
        }

        $categories = [];
        $values = [];
        foreach ($allDays as $day) {
            $categories[] = $day;
            $values[] = isset($data[$day]) ? (int)$data[$day]->count : 0;
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => __('New Users'),
                    'data' => $values
                ]
            ]
        ];
    }

    /**
     * Count users by status in a given time range.
     */
    protected static function countUsersByStatus($from, $to)
    {
        $query = User::whereBetween('created', [$from, $to]);

        return [
            'total'    => $query->count(),
            'active'   => (clone $query)->where('status', 2)->count(),
            'inactive' => (clone $query)->where('status', 1)->count(),
            'banned'   => (clone $query)->where('status', 0)->count(),
        ];
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