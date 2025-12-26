<?php
namespace Modules\AppAnalyticsFacebook\Services;

use Modules\AppChannels\Models\Accounts;
use Modules\AppAnalytics\Models\SocialAnalytics;
use Modules\AppAnalytics\Models\SocialAnalyticsSnapshot;
use Modules\AppAnalytics\Models\SocialAnalyticsPost;
use Modules\AppAnalytics\Models\SocialAnalyticsPostInfo;
use Modules\AppAnalytics\Models\SocialAnalyticsLog;
use Modules\AppAnalytics\Contracts\SocialAnalyticsInterface;
use JanuSoftware\Facebook\Facebook;
use Carbon\Carbon;
use DB;

class FacebookAnalytics implements SocialAnalyticsInterface
{
    protected Facebook $fb;

	public function __construct()
	{
	    try {
	        $this->fb = $this->getFacebookClient();
	    } catch (\Exception $e) {}
	}
	
	protected function getFacebookClient(): Facebook
	{
	    $appId = get_option("facebook_app_id");
	    $appSecret = get_option("facebook_app_secret");
	    $appVersion = get_option("facebook_graph_version", "v22.0");

	    if (!$appId || !$appSecret || !$appVersion) {
	        throw new \Exception(__('Facebook app config is missing. Please check your App ID, App Secret, and Graph Version.'));
	    }

	    return new Facebook([
	        'app_id' => $appId,
	        'app_secret' => $appSecret,
	        'default_graph_version' => $appVersion,
	    ]);
	}

	public function getAccounts(int $teamId)
	{
		$accounts = Accounts::where("team_id", $teamId)->where("social_network", "facebook")->where("category", "page")->orderBy('id')->get();

		if ($accounts) {
			foreach ($accounts as $key => $value) {
				$module = \Module::find($value->module);
				$moduleInfo = $module->get('menu');
				$accounts[$key]->module_icon = $moduleInfo['icon']??'';
				$accounts[$key]->module_color = $moduleInfo['color']??'';
				$accounts[$key]->module_name = $moduleInfo['name']??'';

			}
		}

		return $accounts;
	}

    public function getName(): string
    {
        return 'Facebook';
    }

    public function getAnalyticsData(int $teamId, ?string $id_secure = null, ?string $since = null, ?string $until = null): array
	{
	    $accountInfo = $this->getAccountInfo($teamId, $id_secure);

	    if (!$accountInfo) {
	        return [
	            'status' => 'error',
	            'message' => __('Facebook account not found or disconnected.'),
	        ];
	    }

	    $accountId = $accountInfo['id'];
	    $pageId = $accountInfo['pid'];
	    $accessToken = $accountInfo['pid'];

	    //SYNC NEW DATA
	    $account = Accounts::find($accountId);
	    $this->syncPageInsights($accountId, $pageId, $account->token, $since, $until);
	    $this->syncPostInsights($accountId, $pageId, $account->token, $since, $until);

	    $overview = $this->getFacebookOverview($accountId, $since, $until);
	    $overviewChart = $this->getOverviewChartData($accountId, $since, $until);
	    $dailyPageViewsChartData = $this->getDailyPageViewsChartData($accountId, $since, $until);
	    $fanHistoryChartData = $this->getFanHistoryChartData($accountId, $since, $until);
	    $fansChartData = $this->getFanChangesChartData($accountId, $since, $until);
	    $fanSummary = $this->getFanSummary($accountId, $since, $until);
	    $postReachSummaryChart = $this->getPostReachSummaryChartData($accountId, $since, $until);
	    $postImpressionSummaryChart = $this->getPostImpressionSummaryChartData($accountId, $since, $until);
	    $postEngagementSummaryChart = $this->getPostEngagementSummaryChartData($accountId, $since, $until);
	    $postEngagementRateSummaryData = $this->getPostEngagementRateSummaryData($accountId, $since, $until);
	    $videoViewCompletionChart = $this->getVideoViewCompletionChartData($accountId, $since, $until);
	    $videoOrganicPaidChart= $this->getVideoOrganicPaidChartData($accountId, $since, $until);
	    $videoPlayMethodChart = $this->getVideoPlayMethodChartData($accountId, $since, $until);
	    $postHistoryList = $this->getPostHistoryList($accountId, $since, $until);
	    $fansLocationMapChart = $this->getFansLocationMapChartData($accountId, $since, $until);
	    $topFansCountries = $this->getTopFansCountries($accountId, $since, $until);

	    return [
	        'status' => 'success',
	        'account' => $accountInfo,
	        'overview' => $overview,
	        'overview_chart' => $overviewChart,
	        'fan_summary' => $fanSummary,
	        'fan_history_chart' => $fanHistoryChartData,
	        'gained_lost_fans_chart' => $fansChartData,
	        'page_views_chart' => $dailyPageViewsChartData,
	        'postReachSummaryChart' => $postReachSummaryChart,
	        'postImpressionSummaryChart' => $postImpressionSummaryChart,
	        'postEngagementSummaryChart' => $postEngagementSummaryChart,
	        'postEngagementRateSummary' => $postEngagementRateSummaryData,
	        'videoViewCompletionChart' => $videoViewCompletionChart,
	        'videoOrganicPaidChart' => $videoOrganicPaidChart,
	        'videoPlayMethodChart' => $videoPlayMethodChart,
	        'postHistoryList' => $postHistoryList,
	        'fansLocationMapChart' => $fansLocationMapChart,
	        'topFansCountries' => $topFansCountries
	    ];
	}

	public function getFacebookOverview(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'page_impressions',
	        'page_impressions_unique',
	        'page_post_engagements',
	        'page_views_total',
	        'page_fan_adds_unique',
	        'page_follows',
	    ];

	    $data = SocialAnalytics::where('account_id', $accountId)
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$since, $until])
	        ->get()
	        ->groupBy('metric')
	        ->map(fn($group) => $group->sum('value'))
	        ->all();

	    $days = Carbon::parse($since)->diffInDays(Carbon::parse($until)) + 1;
	    $sinceCompare = Carbon::parse($since)->subDays($days)->toDateString();
	    $untilCompare = Carbon::parse($since)->subDay()->toDateString();

	    $dataCompare = SocialAnalytics::where('account_id', $accountId)
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->get()
	        ->groupBy('metric')
	        ->map(fn($group) => $group->sum('value'))
	        ->all();

	    $currentPosts = SocialAnalyticsPost::where('account_id', $accountId)
	        ->whereBetween('date', [$since, $until])
	        ->count();

	    $previousPosts = SocialAnalyticsPost::where('account_id', $accountId)
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->count();

	    $calculateChange = function ($current, $previous) {
	        if ($previous == 0 && $current == 0) return 0;
	        if ($previous == 0) return 100;
	        return round((($current - $previous) / $previous) * 100, 2);
	    };

	    return [
	        'likes' => [
	            'value' => (int) ($data['page_fan_adds_unique'] ?? 0),
	            'change' => $calculateChange($data['page_fan_adds_unique'] ?? 0, $dataCompare['page_fan_adds_unique'] ?? 0),
	        ],
	        'reach' => [
	            'value' => (int) ($data['page_impressions_unique'] ?? 0),
	            'change' => $calculateChange($data['page_impressions_unique'] ?? 0, $dataCompare['page_impressions_unique'] ?? 0),
	        ],
	        'impressions' => [
	            'value' => (int) ($data['page_impressions'] ?? 0),
	            'change' => $calculateChange($data['page_impressions'] ?? 0, $dataCompare['page_impressions'] ?? 0),
	        ],
	        'engagements' => [
	            'value' => (int) ($data['page_post_engagements'] ?? 0),
	            'change' => $calculateChange($data['page_post_engagements'] ?? 0, $dataCompare['page_post_engagements'] ?? 0),
	        ],
	        'page_views' => [
	            'value' => (int) ($data['page_views_total'] ?? 0),
	            'change' => $calculateChange($data['page_views_total'] ?? 0, $dataCompare['page_views_total'] ?? 0),
	        ],
	        'published_posts' => [
	            'value' => $currentPosts,
	            'change' => $calculateChange($currentPosts, $previousPosts),
	        ],
	    ];
	}

	public function getOverviewChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'page_impressions'        => __('Impressions'),
	        'page_impressions_unique' => __('Reach'),
	        'page_post_engagements'   => __('Engagements'),
	    ];

	    $rawData = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            'metric',
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day', 'metric')
	        ->orderBy('day')
	        ->get();

	    $days = collect();
	    $start = \Carbon\Carbon::parse($since);
	    $end = \Carbon\Carbon::parse($until);
	    while ($start->lte($end)) {
	        $days->push($start->format('M d'));
	        $start->addDay();
	    }

	    $grouped = $rawData->groupBy('metric')->map(function ($rows) {
	        return $rows->keyBy('day');
	    });

	    $series = [];

	    foreach ($metrics as $metricKey => $label) {
	        $dataArr = $days->map(function ($day) use ($grouped, $metricKey) {
	            return isset($grouped[$metricKey][$day])
	                ? (int) $grouped[$metricKey][$day]->total
	                : 0;
	        })->toArray();

	        $series[] = [
	            'name' => $label,
	            'data' => $dataArr
	        ];
	    }

	    return [
	        'series'     => $series,
	        'categories' => $days->toArray()
	    ];
	}

	public function getFanSummary(int $accountId, string $since, string $until): array
	{
	    $metricsMap = [
	        'page_fan_adds_unique'    => 'new_fans',
	        'page_fan_removes_unique' => 'lost_fans',
	    ];

	    $raw = SocialAnalytics::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->whereIn('metric', array_keys($metricsMap))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->pluck('total', 'metric')
	        ->toArray();

	    $result = [];

	    foreach ($metricsMap as $metric => $key) {
	        $result[$key] = (int) ($raw[$metric] ?? 0);
	    }

	    $result['net_fans'] = $result['new_fans'] - $result['lost_fans'];

	    return $result;
	}

	public function getFanHistoryChartData(int $accountId, string $since, string $until): array
	{
	    $dates = collect();
	    $start = \Carbon\Carbon::parse($since);
	    $end = \Carbon\Carbon::parse($until);
	    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
	        $dates->put($date->format('M d'), 0);
	    }

	    $raw = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            DB::raw('MAX(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('metric', 'page_fans')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderByRaw('STR_TO_DATE(day, "%b %d")')
	        ->get()
	        ->keyBy('day');

	    $seriesData = $dates->map(fn($v, $day) => (int) ($raw[$day]->total ?? 0))->toArray();

	    return [
	        'categories' => $dates->keys()->toArray(),
	        'series' => [
	            [
	                'name' => __('Fans'),
	                'data' => array_values($seriesData),
	            ]
	        ]
	    ];
	}

	public function getFanChangesChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'page_fan_adds_unique'    => __('Gained Fans'),
	        'page_fan_removes_unique' => __('Lost Fans'),
	    ];

	    $dates = collect();
	    $start = \Carbon\Carbon::parse($since);
	    $end = \Carbon\Carbon::parse($until);
	    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
	        $dates->put($date->format('M d'), 0);
	    }

	    $raw = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            'metric',
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day', 'metric')
	        ->orderByRaw('STR_TO_DATE(day, "%b %d")')
	        ->get()
	        ->groupBy('metric');

	    $series = [];
	    foreach ($metrics as $metricKey => $label) {
	        $data = $dates->map(function ($v, $day) use ($raw, $metricKey) {
	            return isset($raw[$metricKey])
	                ? (int) ($raw[$metricKey]->firstWhere('day', $day)?->total ?? 0)
	                : 0;
	        })->toArray();

	        $series[] = [
	            'name' => $label,
	            'data' => array_values($data)
	        ];
	    }

	    return [
	        'categories' => $dates->keys()->toArray(),
	        'series' => $series
	    ];
	}

	public function getDailyPageViewsChartData(int $accountId, string $since, string $until): array
	{
	    $dateRange = collect();
	    $start = \Carbon\Carbon::parse($since);
	    $end = \Carbon\Carbon::parse($until);
	    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
	        $dateRange->put($date->format('M d'), 0);
	    }

	    $raw = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('metric', 'page_views_total')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderByRaw('STR_TO_DATE(day, "%b %d")')
	        ->get()
	        ->keyBy('day');

	    $values = $dateRange->map(fn($v, $day) =>
	        isset($raw[$day]) ? (int) $raw[$day]->total : 0
	    );

	    return [
	        'categories' => $values->keys()->toArray(),
	        'series' => [
	            ['name' => __('Page Views'), 'data' => $values->values()->toArray()]
	        ],
	        'summary' => [
	            'total' => $values->sum()
	        ]
	    ];
	}

	public function getPostReachSummaryChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'post_impressions_unique'         => __('Total Reach'),
	        'post_impressions_organic_unique' => __('Organic Reach'),
	        'post_impressions_paid_unique'    => __('Paid Reach'),
	    ];

	    $raw = SocialAnalyticsPostInfo::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->get()
	        ->pluck('total', 'metric');

	    $totals = [];
	    foreach (array_keys($metrics) as $key) {
	        $totals[$key] = (int) ($raw[$key] ?? 0);
	    }

	    $days = Carbon::parse($since)->diffInDays(Carbon::parse($until)) + 1;

	    return [
	        'summary' => [
	            'total_reach' => $totals['post_impressions_unique'],
	            'organic'     => $totals['post_impressions_organic_unique'],
	            'paid'        => $totals['post_impressions_paid_unique'],
	            'avg_daily'   => $days > 0 ? round($totals['post_impressions_unique'] / $days) : 0,
	        ],
	        'series' => [
	            [
	                'name' => __('Reach'),
	                'data' => [
	                    $totals['post_impressions_unique'],
	                    $totals['post_impressions_organic_unique'],
	                    $totals['post_impressions_paid_unique'],
	                ],
	                'colorByPoint' => true
	            ]
	        ],
	        'categories' => array_values($metrics)
	    ];
	}

	public function getPostImpressionSummaryChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'post_impressions'         => __('Total Impressions'),
	        'post_impressions_organic' => __('Organic Impressions'),
	        'post_impressions_paid'    => __('Paid Impressions'),
	    ];

	    $raw = SocialAnalyticsPostInfo::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->get()
	        ->pluck('total', 'metric');

	    $totals = [];
	    foreach (array_keys($metrics) as $key) {
	        $totals[$key] = (int) ($raw[$key] ?? 0);
	    }

	    $days = Carbon::parse($since)->diffInDays(Carbon::parse($until)) + 1;

	    return [
	        'summary' => [
	            'total'     => $totals['post_impressions'],
	            'organic'   => $totals['post_impressions_organic'],
	            'paid'      => $totals['post_impressions_paid'],
	            'avg_daily' => $days > 0 ? round($totals['post_impressions'] / $days) : 0,
	        ],
	        'series' => [
	            [
	                'name' => __('Impressions'),
	                'data' => [
	                    $totals['post_impressions'],
	                    $totals['post_impressions_organic'],
	                    $totals['post_impressions_paid'],
	                ],
	                'colorByPoint' => true
	            ]
	        ],
	        'categories' => array_values($metrics)
	    ];
	}

	public function getPostEngagementSummaryChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'likes'     => __('Likes'),
	        'comments'  => __('Comments'),
	        'shares'    => __('Shares'),
	        'reactions' => __('Reactions'),
	    ];

	    $raw = SocialAnalyticsPostInfo::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->get()
	        ->pluck('total', 'metric');

	    // Chuẩn hóa kết quả về 0 nếu không có
	    $totals = [];
	    foreach (array_keys($metrics) as $key) {
	        $totals[$key] = (int) ($raw[$key] ?? 0);
	    }

	    return [
	        'summary' => [
	            'likes'     => $totals['likes'],
	            'comments'  => $totals['comments'],
	            'shares'    => $totals['shares'],
	            'reactions' => $totals['reactions'],
	            'total'     => array_sum($totals),
	        ],
	        'series' => [
	            [
	                'name' => __('Engagements'),
	                'data' => array_values($totals),
	                'colorByPoint' => true
	            ]
	        ],
	        'categories' => array_values($metrics),
	    ];
	}

	public function getPostEngagementRateSummaryData(int $accountId, string $since, string $until): array
	{
	    $metrics = ['reactions', 'comments', 'shares', 'post_impressions'];

	    $totals = SocialAnalyticsPostInfo::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->get()
	        ->pluck('total', 'metric');

	    // Normalize values
	    $values = [];
	    foreach ($metrics as $metric) {
	        $values[$metric] = (int) ($totals[$metric] ?? 0);
	    }

	    $engagements = $values['reactions'] + $values['comments'] + $values['shares'];
	    $impressions = $values['post_impressions'];
	    $rate = $impressions > 0 ? round(($engagements / $impressions) * 100, 2) : 0;

	    return [
	        'summary' => [
	            'engagements' => $engagements,
	            'impressions' => $impressions,
	            'rate'        => $rate,
	            'reactions'   => $values['reactions'],
	            'comments'    => $values['comments'],
	            'shares'      => $values['shares'],
	        ]
	    ];
	}

	public function getVideoViewCompletionChartData(int $accountId, string $since, string $until): array
	{
	    $fullViews = (int) SocialAnalytics::query()
	        ->where('account_id', $accountId)
	        ->where('metric', 'page_video_complete_views_30s_organic')
	        ->whereBetween('date', [$since, $until])
	        ->sum('value');

	    $totalViews = (int) SocialAnalytics::query()
	        ->where('account_id', $accountId)
	        ->where('metric', 'page_video_views_organic')
	        ->whereBetween('date', [$since, $until])
	        ->sum('value');

	    $partialViews = max(0, $totalViews - $fullViews);

	    return [
	        [
	            'name' => __('Organic Full Views'),
	            'y'    => $fullViews,
	        ],
	        [
	            'name' => __('Organic Partial Views'),
	            'y'    => $partialViews,
	        ]
	    ];
	}

	public function getVideoOrganicPaidChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'page_video_views_organic' => __('Organic Views'),
	        'page_video_views_paid'    => __('Paid Views'),
	    ];

	    $data = SocialAnalytics::select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->pluck('total', 'metric');

	    return collect($metrics)->map(function ($label, $metric) use ($data) {
	        return [
	            'name' => $label,
	            'y'    => (int) ($data[$metric] ?? 0),
	        ];
	    })->values()->toArray();
	}

	public function getVideoPlayMethodChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'page_video_views_click_to_play' => __('Click Plays'),
	        'page_video_views_autoplayed'    => __('Auto Plays'),
	    ];

	    $data = SocialAnalytics::select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->pluck('total', 'metric');

	    return collect($metrics)->map(function ($label, $metric) use ($data) {
	        return [
	            'name' => $label,
	            'y'    => (int) ($data[$metric] ?? 0),
	        ];
	    })->values()->toArray();
	}

	public function getTopFansCountries(int $accountId, string $since, string $until): array
	{
	    $raw = DB::table('social_analytics')
	        ->selectRaw("REPLACE(metric, 'page_fans_country.', '') as code, MAX(value) as fans")
	        ->where('account_id', $accountId)
	        ->whereBetween('date', [$since, $until])
	        ->where('metric', 'like', 'page_fans_country.%')
	        ->groupBy('code')
	        ->havingRaw('fans > 0')
	        ->orderByDesc('fans')
	        ->limit(10)
	        ->get();

	    return $raw->map(function ($row) {
	        $countryCode = strtoupper($row->code);
	        return [
	            'country' => list_countries($countryCode),
	            'code'    => $countryCode,
	            'fans'    => (int) $row->fans,
	        ];
	    })->toArray();
	}

	public function getFansLocationMapChartData(int $accountId, string $since, string $until): array
	{
	    $raw = SocialAnalytics::selectRaw("
	            REPLACE(REPLACE(metric, 'page_fans_country.', ''), 'page_fans_locale.', '') as country_code,
	            MAX(value) as fans
	        ")
	        ->where('account_id', $accountId)
	        ->whereBetween('date', [$since, $until])
	        ->where(function ($q) {
	            $q->where('metric', 'like', 'page_fans_country.%')
	              ->orWhere('metric', 'like', 'page_fans_locale.%');
	        })
	        ->groupBy('country_code')
	        ->havingRaw('fans > 0')
	        ->get();

	    return $raw->map(function ($row) {
	        $code = strtoupper(substr($row->country_code, 0, 2));
	        return [
	            'code'  => $code,
	            'value' => (int) $row->fans,
	        ];
	    })->values()->toArray();
	}

	public function getPostHistoryList(int $accountId, string $since, string $until): array
	{
	    $meta = SocialAnalyticsPost::query()
	        ->where('account_id', $accountId)
	        ->whereBetween('date', [$since, $until])
	        ->orderByDesc('created_time')
	        ->get()
	        ->keyBy('post_id');

	    $metrics = SocialAnalyticsPostInfo::query()
	        ->select('post_id', 'metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->whereBetween('date', [$since, $until])
	        ->whereIn('metric', [
	            'post_impressions',
	            'post_impressions_unique',
	            'likes',
	            'shares',
	            'comments',
	            'reactions',
	        ])
	        ->groupBy('post_id', 'metric')
	        ->get()
	        ->groupBy('post_id');

	    $posts = [];

	    foreach ($meta as $postId => $post) {
	        $metricData = $metrics[$postId] ?? collect();

	        $posts[] = [
	            'post_id'       => $post->post_id,
	            'message'       => $post->message,
	            'created_time'  => $post->created_time,
	            'full_picture'  => $post->full_picture,
	            'permalink_url' => $post->permalink_url,
	            'metrics'       => $metricData->pluck('total', 'metric')
	                                          ->map(fn($val) => (int) $val)
	                                          ->toArray(),
	        ];
	    }

	    return $posts;
	}


	// -------------------------------------
	// SYNCING NEW ANALYTICS DATA
	// -------------------------------------
	protected function getAccountInfo(int $teamId, ?string $id_secure = null): ?array
	{
	    $account = Accounts::where('social_network', 'facebook')
	        ->where('category', 'page')
	        ->where('login_type', 1)
	        ->where('team_id', $teamId)
	        ->when($id_secure, fn($q) => $q->where('id_secure', $id_secure))
	        ->first();

	    if (!$account) {
	        logger()->warning("[FacebookAnalytics] No account found for team_id={$teamId}");
	        return null;
	    }

	    $now = time();

	    if (\Analytics::shouldFetchSocialAnalytics($account->id, 'facebook', 'account')) {
	        try {
	            $response = $this->fb->get("/{$account->pid}?fields=name,username,picture.type(large),cover,id,category,talking_about_count,fan_count,followers_count,rating_count", $account->token);
	            $page = $response->getDecodedBody();

	            $info = [
	                'id' => $account->id,
	                'pid' => $account->pid,
	                'name' => $page['name'] ?? $account->name,
	                'username' => $page['username'] ?? $account->username,
	                'url' => $account->url,
	                'avatar' => $page['picture']['data']['url'] ?? theme_public_asset('img/default.png'),
	                'cover' => $page['cover']['source'] ?? null,
	                'category' => $page['category'] ?? null,
	                'fan_count' => $page['fan_count'] ?? 0,
	                'followers_count' => $page['followers_count'] ?? 0,
	                'talking_about' => $page['talking_about_count'] ?? 0,
	                'rating_count' => $page['rating_count'] ?? 0,
	            ];

	            SocialAnalyticsSnapshot::updateOrCreate(
	                [
	                    'account_id' => $account->id,
	                    'social_network' => 'facebook',
	                    'date' => now()->toDateString(),
	                ],
	                [
	                    'data' => $info,
	                    'created' => $now,
	                ]
	            );

	            // Đảm bảo trả về token ở cuối cùng
	            $info['token'] = $account->token;

	            \Analytics::markSynced($account->id, 'facebook', 'account');
	            return $info;

	        } catch (\Exception $e) {
	            logger()->error("[FacebookAnalytics] getAccountInfo error: " . $e->getMessage());
	        }
	    }

	    $snapshot = SocialAnalyticsSnapshot::where([
	        'account_id' => $account->id,
	        'social_network' => 'facebook',
	        'date' => now()->toDateString(),
	    ])->first();

	    if ($snapshot && $snapshot->data) {
	        logger()->info("[FacebookAnalytics] Using snapshot data from DB.");
	        $data = is_array($snapshot->data) ? $snapshot->data : json_decode($snapshot->data, true);
	        $data['token'] = $account->token;
	        return $data;
	    }

	    return null;
	}

	protected function syncPageInsights(int $accountId, string $pageId, string $token, string $since, string $until): void
	{
	    $social = 'facebook';

	    $metrics = [
	        'page_impressions',
	        'page_impressions_paid',
	        'page_impressions_unique',
	        'page_impressions_paid_unique',
	        'page_actions_post_reactions_total',
	        'page_post_engagements',
	        'page_views_total',
	        'page_fan_adds_unique',
	        'page_fan_removes_unique',
	        'page_follows',
	        'page_fans_locale',
	        'page_fans_country',
	        'page_video_complete_views_30s_organic',
	        'page_video_views_organic',
	        'page_video_views_paid',
	        'page_video_views_autoplayed',
	        'page_video_views_click_to_play',
	    ];

	    if (!\Analytics::shouldFetchSocialAnalytics($accountId, $social, 'page')) {
	        return;
	    }

	    try {
	        $endpoint = "/{$pageId}/insights?metric=" . implode(',', $metrics) .
	            "&since={$since}&until={$until}&period=day";

	        $response = $this->fb->get($endpoint, $token);
	        $result = $response->getDecodedBody();

	        $insights = [];

	        foreach ($result['data'] ?? [] as $item) {
	            $metric = $item['name'];

	            foreach ($item['values'] as $entry) {
	                $date = Carbon::parse($entry['end_time'])->toDateString();

	                if (is_array($entry['value'])) {
	                    if (in_array($metric, ['page_fans_locale', 'page_fans_country'])) {
	                        foreach ($entry['value'] as $key => $count) {
	                            if ((float) $count > 0) {
	                                $insights["{$metric}.{$key}"][$date] = (float) $count;
	                            }
	                        }
	                    }
	                } else {
	                    $value = (float) $entry['value'];
	                    if ($value > 0) {
	                        $insights[$metric][$date] = $value;
	                    }
	                }
	            }
	        }

	        if (isset($insights['page_impressions'], $insights['page_impressions_paid'])) {
	            foreach ($insights['page_impressions'] as $date => $total) {
	                $paid = $insights['page_impressions_paid'][$date] ?? 0;
	                $organic = $total - $paid;
	                if ($organic > 0) {
	                    $insights['page_impressions_organic'][$date] = $organic;
	                }
	            }
	        }

	        if (!empty($insights)) {
	            \Analytics::saveInsightsToDatabase($accountId, $social, $insights);
	            \Analytics::markSynced($accountId, $social, 'page');
	        }

	    } catch (\Exception $e) {
	        logger()->error("[FacebookAnalytics] syncPageInsights error: " . $e->getMessage());
	    }
	}

	protected function syncPostInsights(int $accountId, string $pageId, string $token, string $since, string $until): void
	{
	    $social = 'facebook';
	    $now = time();

	    $metricList = [
	        'post_impressions',
	        'post_impressions_paid',
	        'post_impressions_organic',
	        'post_impressions_unique',
	        'post_impressions_paid_unique',
	        'post_impressions_organic_unique',
	    ];

	    if (!\Analytics::shouldFetchSocialAnalytics($accountId, $social, 'post')) {
	        return;
	    }

	    try {
	        $url = "/{$pageId}/published_posts?fields=" .
	            "message,created_time,full_picture,attachments,permalink_url,status_type," .
	            "likes.summary(true),comments.summary(true),shares," .
	            "reactions.summary(true)," .
	            "insights.metric(" . implode(',', $metricList) . ")" .
	            "&since={$since}&until={$until}&period=day&limit=100";

	        $response = $this->fb->get($url, $token);
	        $posts = $response->getDecodedBody();

	        $metaInsert = [];
	        $metricsInsert = [];

	        foreach ($posts['data'] ?? [] as $post) {
	            $postId = $post['id'];
	            $created = Carbon::parse($post['created_time']);
	            $date = $created->toDateString();

	            $metaInsert[] = [
	                'account_id'     => $accountId,
	                'social_network' => $social,
	                'post_id'        => $postId,
	                'date'           => $date,
	                'message'        => $post['message'] ?? null,
	                'created_time'   => $created->toDateTimeString(),
	                'full_picture'   => $post['full_picture'] ?? null,
	                'permalink_url'  => $post['permalink_url'] ?? null,
	                'type'           => $post['type'] ?? null,
	                'status_type'    => $post['status_type'] ?? null,
	                'created'        => $now,
	            ];

	            foreach ([
	                'likes'     => $post['likes']['summary']['total_count'] ?? 0,
	                'comments'  => $post['comments']['summary']['total_count'] ?? 0,
	                'shares'    => $post['shares']['count'] ?? 0,
	                'reactions' => $post['reactions']['summary']['total_count'] ?? 0
	            ] as $metric => $value) {
	                if ((int)$value > 0) {
	                    $metricsInsert[] = [
	                        'post_id'        => $postId,
	                        'account_id'     => $accountId,
	                        'social_network' => $social,
	                        'metric'         => $metric,
	                        'value'          => (float) $value,
	                        'date'           => $date,
	                        'created'        => $now,
	                    ];
	                }
	            }

	            $insights = collect($post['insights']['data'] ?? [])->keyBy('name');
	            foreach ($metricList as $metric) {
	                $val = $insights[$metric]['values'][0]['value'] ?? 0;
	                if ((int) $val > 0) {
	                    $metricsInsert[] = [
	                        'post_id'        => $postId,
	                        'account_id'     => $accountId,
	                        'social_network' => $social,
	                        'metric'         => $metric,
	                        'value'          => (int) $val,
	                        'date'           => $date,
	                        'created'        => $now,
	                    ];
	                }
	            }
	        }

	        if (!empty($metaInsert)) {
	            SocialAnalyticsPost::upsert(
	                $metaInsert,
	                ['account_id', 'social_network', 'post_id', 'date'],
	                ['message', 'created_time', 'full_picture', 'permalink_url', 'type', 'status_type', 'created']
	            );
	        }

	        if (!empty($metricsInsert)) {
	            SocialAnalyticsPostInfo::upsert(
	                $metricsInsert,
	                ['post_id', 'metric', 'date'],
	                ['value', 'created']
	            );

	            \Analytics::markSynced($accountId, $social, 'post');
	        }

	    } catch (\Exception $e) {
	        logger()->error("[FacebookAnalytics] syncPostInsights error: " . $e->getMessage());
	    }
	}


}
