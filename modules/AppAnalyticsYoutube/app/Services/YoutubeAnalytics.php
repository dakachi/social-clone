<?php
namespace Modules\AppAnalyticsYoutube\Services;

use Modules\AppChannels\Models\Accounts;
use Modules\AppAnalytics\Models\SocialAnalytics;
use Modules\AppAnalytics\Models\SocialAnalyticsSnapshot;
use Modules\AppAnalytics\Models\SocialAnalyticsPost;
use Modules\AppAnalytics\Models\SocialAnalyticsPostInfo;
use Modules\AppAnalytics\Models\SocialAnalyticsLog;
use Modules\AppAnalytics\Contracts\SocialAnalyticsInterface;
use Google\Service\YouTubeAnalytics as YTAnalytics;
use Google\Client;
use Google\Service\YouTube;
use Google\Service\Oauth2;
use Carbon\Carbon;
use DB;

class YoutubeAnalytics implements SocialAnalyticsInterface
{

	protected $client;
    protected $youtube;
    public $ytAnalytics;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(get_option("youtube_client_id"));
        $this->client->setClientSecret(get_option("youtube_client_secret"));
        $this->client->setDeveloperKey(get_option("youtube_api_key"));
        $this->client->setApplicationName("YouTube Analytics");
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        $this->client->setScopes([
            'https://www.googleapis.com/auth/youtube.readonly',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/yt-analytics.readonly'
        ]);

        $this->ytAnalytics = new YTAnalytics($this->client);
        $this->youtube = new YouTube($this->client);
    }

    public function getName(): string
    {
        return 'Youtube';
    }

    public function getAccounts(int $teamId)
	{
		$accounts = Accounts::where("team_id", $teamId)->where("social_network", "youtube")->where("category", "channel")->orderBy('id')->get();

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

    public function getAnalyticsData(int $teamId, ?string $id_secure = null, ?string $since = null, ?string $until = null): array
	{
	    $accountInfo = $this->getAccountInfo($teamId, $id_secure);

	    if (!$accountInfo) {
	        return [
	            'status' => 'error',
	            'message' => __('YouTube account not found or disconnected.'),
	        ];
	    }

	    $accountId = $accountInfo['id'];
	    $this->syncAccount($accountId, $since, $until);
	    $overview = $this->getOverview($accountId, $since, $until);
	    $dailyViewsChartData = $this->getDailyViewsChartData($accountId, $since, $until);
	    $dailyEngagementChartData = $this->getDailyEngagementChartData($accountId, $since, $until);
	    $dailySubscribersChartData = $this->getDailySubscribersChartData($accountId, $since, $until);
	    $viewsLocationMapChartData = $this->getViewsLocationMapChartData($accountId, $since, $until);
	    $topCountriesByViews = $this->getTopCountriesByViews($accountId, $since, $until);
	    $viewsByTrafficSourceChartData = $this->getViewsByTrafficSourceChartData($accountId, $since, $until);
	    $viewsByDeviceChartData = $this->getViewsByDeviceChartData($accountId, $since, $until);
	    $estimatedMinutesWatchedChartData = $this->getEstimatedMinutesWatchedChartData($accountId, $since, $until);
	    $averageViewDurationChartData = $this->getAverageViewDurationChartData($accountId, $since, $until);
	    $viewerByAgeChartData = $this->getViewerByAgeChartData($accountId, $since, $until);
	    $viewerByGenderChartData = $this->getViewerByGenderChartData($accountId, $since, $until);
	    $videoHistoryList = $this->getVideoHistoryList($accountId, $since, $until);

	    return [
	        'status' => 'success',
	        'account' => $accountInfo,
	        'overview' => $overview,
	        'dailyViewsChartData' => $dailyViewsChartData,
	        'dailyEngagementChartData' => $dailyEngagementChartData,
	        'dailySubscribersChartData' => $dailySubscribersChartData,
	        'viewsLocationMapChartData' => $viewsLocationMapChartData,
	        'topCountriesByViews' => $topCountriesByViews,
	        'viewsByTrafficSourceChartData' => $viewsByTrafficSourceChartData,
	        'viewsByDeviceChartData' => $viewsByDeviceChartData,
	        'averageViewDurationChartData' => $averageViewDurationChartData,
	        'estimatedMinutesWatchedChartData' => $estimatedMinutesWatchedChartData,
	        'viewerByAgeChartData' => $viewerByAgeChartData,
	        'viewerByGenderChartData' => $viewerByGenderChartData,
	        'videoHistoryList' => $videoHistoryList,
	    ];
	}

	protected function getAccountInfo(int $teamId, ?string $id_secure = null): ?array
	{
	    $account = Accounts::where('social_network', 'youtube')
	        ->where('category', 'channel')
	        ->where('login_type', 1)
	        ->where('team_id', $teamId)
	        ->when($id_secure, fn($q) => $q->where('id_secure', $id_secure))
	        ->first();

	    if (!$account) {
	        logger()->warning("[YoutubeAnalytics] No account found for team_id={$teamId}");
	        return null;
	    }

	    $now = time();

	    if (\Analytics::shouldFetchSocialAnalytics($account->id, 'youtube', 'account')) {
	        try {
	            $token = json_decode($account->token, true);
	            $this->client->setAccessToken($token);

	            if ($this->client->isAccessTokenExpired()) {
	                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
	                $token = $this->client->getAccessToken();
	                $account->token = json_encode($token);
	                $account->save();
	            }

	            $this->youtube = new YouTube($this->client);

	            $response = $this->youtube->channels->listChannels('snippet,statistics', [
	                'id' => $account->pid,
	            ]);

	            $channel = $response[0];

	            $info = [
	                'id' => $account->id,
	                'pid' => $account->pid,
	                'name' => $channel->getSnippet()->getTitle(),
	                'username' => $account->username,
	                'avatar' => $channel->getSnippet()->getThumbnails()->getDefault()->getUrl(),
	                'url' => 'https://www.youtube.com/channel/' . $account->pid,
	                'subscribers' => $channel->getStatistics()->getSubscriberCount(),
	                'views' => $channel->getStatistics()->getViewCount(),
	                'video_count' => $channel->getStatistics()->getVideoCount(),
	            ];

	            SocialAnalyticsSnapshot::updateOrCreate(
	                [
	                    'account_id' => $account->id,
	                    'social_network' => 'youtube',
	                    'date' => now()->toDateString(),
	                ],
	                [
	                    'data' => $info,
	                    'created' => $now,
	                ]
	            );

	            $account->data = json_encode($info);
	            $account->created = $now;
	            $account->save();

	            \Analytics::markSynced($account->id, 'youtube', 'account');

	            return $info;

	        } catch (\Exception $e) {
	            logger()->error("[YoutubeAnalytics] getAccountInfo error: " . $e->getMessage());
	        }
	    }

	    $snapshot = SocialAnalyticsSnapshot::where([
	        'account_id' => $account->id,
	        'social_network' => 'youtube',
	        'date' => now()->toDateString(),
	    ])->first();

	    if ($snapshot && $snapshot->data) {
	        logger()->info("[YoutubeAnalytics] Using snapshot data from DB.");
	        return $snapshot->data;
	    }

	    return null;
	}

	public function getOverview(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'views',
	        'likes',
	        'comments',
	        'shares',
	        'subscribersGained',
	        'subscribersLost',
	    ];

	    $current = SocialAnalytics::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->pluck('total', 'metric');

	    $totalPublishedCurrent = DB::table('social_analytics_posts')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->whereBetween('date', [$since, $until])
	        ->count();

	    $days = \Carbon\Carbon::parse($since)->diffInDays(\Carbon\Carbon::parse($until)) + 1;
	    $sinceCompare = \Carbon\Carbon::parse($since)->subDays($days)->toDateString();
	    $untilCompare = \Carbon\Carbon::parse($since)->subDay()->toDateString();

	    $previous = SocialAnalytics::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->groupBy('metric')
	        ->pluck('total', 'metric');

	    $totalPublishedPrevious = DB::table('social_analytics_posts')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->count();

	    $currentNetSubscribers = (int)($current['subscribersGained'] ?? 0) - (int)($current['subscribersLost'] ?? 0);
	    $previousNetSubscribers = (int)($previous['subscribersGained'] ?? 0) - (int)($previous['subscribersLost'] ?? 0);

	    $calculateChange = function ($current, $previous) {
	        if ($previous == 0 && $current == 0) return 0;
	        if ($previous == 0) return 100;
	        return round((($current - $previous) / $previous) * 100, 2);
	    };

	    return [
	        'views' => [
	            'value' => (int)($current['views'] ?? 0),
	            'change' => $calculateChange($current['views'] ?? 0, $previous['views'] ?? 0),
	        ],
	        'likes' => [
	            'value' => (int)($current['likes'] ?? 0),
	            'change' => $calculateChange($current['likes'] ?? 0, $previous['likes'] ?? 0),
	        ],
	        'comments' => [
	            'value' => (int)($current['comments'] ?? 0),
	            'change' => $calculateChange($current['comments'] ?? 0, $previous['comments'] ?? 0),
	        ],
	        'shares' => [
	            'value' => (int)($current['shares'] ?? 0),
	            'change' => $calculateChange($current['shares'] ?? 0, $previous['shares'] ?? 0),
	        ],
	        'subscribers' => [
			    'value' => $currentNetSubscribers,
			    'change' => $calculateChange($currentNetSubscribers, $previousNetSubscribers),
			    'direction' => $currentNetSubscribers > $previousNetSubscribers ? 'up' : ($currentNetSubscribers < $previousNetSubscribers ? 'down' : 'same'),
			    'is_negative' => $currentNetSubscribers < 0,
			],
	        'published_videos' => [
	            'value' => $totalPublishedCurrent,
	            'change' => $calculateChange($totalPublishedCurrent, $totalPublishedPrevious),
	        ],
	    ];
	}

	public function getDailyViewsChartData(int $accountId, string $since, string $until): array
	{
	    // Generate all days between since and until
	    $period = Carbon::parse($since)->toPeriod(Carbon::parse($until));
	    $allDays = collect($period)->map(fn($date) => $date->format('M d'));

	    // Query actual data
	    $raw = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->where('metric', 'views')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderBy('day')
	        ->get()
	        ->keyBy('day');

	    // Build complete data set with 0 fallback
	    $seriesData = $allDays->map(fn($day) => (int) ($raw[$day]->total ?? 0))->toArray();
	    $categories = $allDays->toArray();
	    $total = array_sum($seriesData);

	    return [
	        'categories' => $categories,
	        'series' => [
	            [
	                'name' => __('Views'),
	                'data' => $seriesData
	            ]
	        ],
	        'summary' => [
	            'total' => $total
	        ]
	    ];
	}

	public function getDailyEngagementChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'likes'    => __('Likes'),
	        'comments' => __('Comments'),
	        'shares'   => __('Shares'),
	    ];

	    $period = Carbon::parse($since)->toPeriod(Carbon::parse($until));
	    $allDays = collect($period)->map(fn($date) => $date->format('M d'));

	    $raw = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            'metric',
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day', 'metric')
	        ->orderBy('day')
	        ->get()
	        ->groupBy('metric');

	    $series = [];
	    $totals = [];
	    $engagementByDay = [];
	    $sumEngagement = 0;

	    foreach ($metrics as $metricKey => $label) {
	        $dataArr = $allDays->map(function ($day) use ($raw, $metricKey) {
	            if (!isset($raw[$metricKey])) return 0;
	            return (int) ($raw[$metricKey]->firstWhere('day', $day)?->total ?? 0);
	        })->toArray();

	        $totals[$metricKey] = array_sum($dataArr);
	        $series[] = [
	            'name' => $label,
	            'data' => $dataArr,
	        ];
	    }

	    foreach (range(0, $allDays->count() - 1) as $i) {
	        $like    = $series[0]['data'][$i] ?? 0;
	        $comment = $series[1]['data'][$i] ?? 0;
	        $share   = $series[2]['data'][$i] ?? 0;
	        $engagement = $like + $comment + $share;
	        $engagementByDay[] = $engagement;
	        $sumEngagement += $engagement;
	    }

	    $totals['engagement'] = $sumEngagement;

	    return [
	        'categories' => $allDays->toArray(),
	        'series' => $series,
	        'summary' => $totals,
	        'engagement_by_day' => $engagementByDay,
	    ];
	}


	public function getDailySubscribersChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'subscribersGained' => __('Gained'),
	        'subscribersLost'   => __('Lost'),
	    ];

	    $period = Carbon::parse($since)->toPeriod(Carbon::parse($until));
	    $allDays = collect($period)->map(fn($date) => $date->format('M d'));

	    $raw = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            'metric',
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->whereIn('metric', array_keys($metrics))
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day', 'metric')
	        ->orderBy('day')
	        ->get()
	        ->groupBy('metric');

	    $gainedArr = $allDays->map(function ($day) use ($raw) {
	        if (!isset($raw['subscribersGained'])) return 0;
	        return (int) ($raw['subscribersGained']->firstWhere('day', $day)?->total ?? 0);
	    })->toArray();

	    $lostArr = $allDays->map(function ($day) use ($raw) {
	        if (!isset($raw['subscribersLost'])) return 0;
	        return (int) ($raw['subscribersLost']->firstWhere('day', $day)?->total ?? 0);
	    })->toArray();

	    $netArr = [];
	    foreach ($allDays as $i => $day) {
	        $netArr[] = $gainedArr[$i] - $lostArr[$i];
	    }

	    $totalGained = array_sum($gainedArr);
	    $totalLost = array_sum($lostArr);
	    $totalNet = array_sum($netArr);

	    return [
	        'categories' => $allDays->toArray(),
	        'series' => [
	            ['name' => __('Gained'), 'data' => $gainedArr, 'color' => '#00b96b', 'type' => 'column'],
	            ['name' => __('Lost'),   'data' => $lostArr,   'color' => '#ff4d4f', 'type' => 'column'],
	            ['name' => __('Net'),    'data' => $netArr,    'color' => '#675dff', 'type' => 'spline'],
	        ],
	        'summary' => [
	            'gained' => $totalGained,
	            'lost'   => $totalLost,
	            'net'    => $totalNet,
	        ]
	    ];
	}

	public function getViewsLocationMapChartData(int $accountId, string $since, string $until): array
	{
	    $raw = SocialAnalytics::selectRaw("
	            REPLACE(metric, 'views_country.', '') as country_code,
	            SUM(value) as views
	        ")
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->whereBetween('date', [$since, $until])
	        ->where('metric', 'like', 'views_country.%')
	        ->groupBy('country_code')
	        ->get();

	    return $raw->filter(function ($row) {
	            return !empty($row->country_code) && strlen($row->country_code) >= 2;
	        })
	        ->map(function ($row) {
	            return [
	                'code' => strtoupper(substr($row->country_code, 0, 2)),
	                'value' => (int) $row->views
	            ];
	        })
	        ->values()
	        ->toArray();
	}

	public function getTopCountriesByViews(int $accountId, string $since, string $until, int $limit = 10): array
	{
	    return SocialAnalytics::select(
	            DB::raw("REPLACE(metric, 'views_country.', '') as code"),
	            DB::raw('SUM(value) as views')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->where('metric', 'like', 'views_country.%')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('code')
	        ->orderByDesc('views')
	        ->limit($limit)
	        ->get()
	        ->filter(fn($row) => !empty($row->code) && strlen($row->code) >= 2)
	        ->map(fn($row) => [
	            'country' => list_countries(strtoupper($row->code)),
	            'code'    => strtoupper($row->code),
	            'views'   => (int) $row->views
	        ])
	        ->toArray();
	}

	public function getViewsByTrafficSourceChartData(int $accountId, string $since, string $until): array
	{
	    $raw = SocialAnalytics::select(
	            DB::raw("REPLACE(metric, 'views_traffic_source.', '') as source"),
	            DB::raw('SUM(value) as views')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->where('metric', 'like', 'views_traffic_source.%')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('source')
	        ->orderByDesc('views')
	        ->get()
	        ->filter(fn($row) => !empty($row->source)); // Avoid null/empty sources

	    return [
	        'categories' => $raw->pluck('source')->map(fn($s) => $this->mapTrafficSourceName($s))->toArray(),
	        'series' => [
	            [
	                'name' => __('Views'),
	                'data' => $raw->pluck('views')->map(fn($v) => (int) $v)->toArray()
	            ]
	        ]
	    ];
	}

	public function getViewsByDeviceChartData(int $accountId, string $since, string $until): array
	{
	    $raw = SocialAnalytics::select(
	            DB::raw("REPLACE(metric, 'views_device.', '') as device"),
	            DB::raw('SUM(value) as views')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->where('metric', 'like', 'views_device.%')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('device')
	        ->orderByDesc('views')
	        ->get()
	        ->filter(fn($row) => !empty($row->device)); // tránh device rỗng

	    return [
	        'categories' => $raw->pluck('device')->map(fn($d) => ucfirst(strtolower($d)))->toArray(),
	        'series' => [
	            [
	                'name' => __('Views'),
	                'data' => $raw->pluck('views')->map(fn($v) => (int) $v)->toArray()
	            ]
	        ]
	    ];
	}

	public function mapTrafficSourceName(string $source): string
	{
	    $names = [
	        'YT_SEARCH'       => __('YouTube Search'),
	        'EXT_URL'         => __('External'),
	        'RELATED_VIDEO'   => __('Suggested Videos'),
	        'PLAYLIST'        => __('Playlist'),
	        'CHANNEL'         => __('Channel Pages'),
	        'NO_LINK_OTHER'   => __('Other'),
	        'SUBSCRIBER'      => __('Subscribers'),
	        'ADVERTISING'     => __('Advertising'),
	        'NOTIFICATION'    => __('Notifications'),
	        'BROWSE'          => __('Browse Features'),
	        'NO_LINK_EMBED'   => __('Embedded Players'),
	        'DIRECT'          => __('Direct or Unknown'),
	        'RECOMMENDED'     => __('Recommended'),
	    ];

	    return $names[strtoupper($source)] ?? ucwords(strtolower(str_replace('_', ' ', $source)));
	}

	public function getEstimatedMinutesWatchedChartData(int $accountId, string $since, string $until): array
	{
	    $allDays = collect();
	    $period = \Carbon\CarbonPeriod::create($since, $until);
	    foreach ($period as $date) {
	        $allDays->push($date->format('M d'));
	    }

	    $raw = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->where('metric', 'estimatedMinutesWatched')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderBy('day')
	        ->get()
	        ->keyBy('day');

	    $data = $allDays->map(fn($day) => (int) ($raw[$day]->total ?? 0))->toArray();

	    $average = count($data) > 0 ? round(array_sum($data) / count($data), 2) : 0;

	    return [
	        'categories' => $allDays->toArray(),
	        'series' => [[
	            'name' => __('Estimated Minutes Watched'),
	            'data' => $data,
	        ]],
	        'summary' => [
	            'average' => $average
	        ]
	    ];
	}

	public function getAverageViewDurationChartData(int $accountId, string $since, string $until): array
	{
	    $allDays = collect();
	    $period = \Carbon\CarbonPeriod::create($since, $until);
	    foreach ($period as $date) {
	        $allDays->push($date->format('M d'));
	    }

	    $raw = SocialAnalytics::select(
	            DB::raw('DATE_FORMAT(date, "%b %d") as day'),
	            DB::raw('AVG(value) as average')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->where('metric', 'averageViewDuration')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderBy('day')
	        ->get()
	        ->keyBy('day');

	    $data = $allDays->map(fn($day) => round((float) ($raw[$day]->average ?? 0), 2))->toArray();
	    $avgAll = count($data) > 0 ? round(array_sum($data) / count($data), 2) : 0;

	    return [
	        'categories' => $allDays->toArray(),
	        'series' => [[
	            'name' => __('Avg Watch Duration (seconds)'),
	            'data' => $data,
	        ]],
	        'summary' => [
	            'average' => $avgAll
	        ]
	    ];
	}

	public function getViewerByAgeChartData(int $accountId, string $since, string $until): array
	{
	    $allAgeGroups = [
	        '13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+'
	    ];

	    $raw = SocialAnalytics::select(
	            DB::raw("REPLACE(metric, 'viewer_age.age', '') as age_group"),
	            DB::raw("AVG(value) as percentage")
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->where('metric', 'like', 'viewer_age.%')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('age_group')
	        ->pluck('percentage', 'age_group');

	    $data = collect($allAgeGroups)->map(fn($age) => round((float)($raw[$age] ?? 0), 2))->toArray();

	    return [
	        'categories' => $allAgeGroups,
	        'series' => [[
	            'name' => __('Viewer %'),
	            'data' => $data,
	            'type' => 'column',
	        ]]
	    ];
	}

	public function getViewerByGenderChartData(int $accountId, string $since, string $until): array
	{
	    $allGenders = [
	        'male' => __('Male'),
	        'female' => __('Female'),
	        'user_specified' => __('User Specified'),
	    ];

	    $raw = SocialAnalytics::select(
	            DB::raw("REPLACE(metric, 'viewer_gender.', '') as gender"),
	            DB::raw("AVG(value) as percentage")
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->where('metric', 'like', 'viewer_gender.%')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('gender')
	        ->pluck('percentage', 'gender');

	    return collect($allGenders)->map(function ($label, $key) use ($raw) {
	        return [
	            'name' => $label,
	            'y'    => round((float)($raw[$key] ?? 0), 2),
	        ];
	    })->values()->toArray();
	}

	public function getVideoHistoryList(int $accountId, string $since, string $until): array
	{
	    $meta = SocialAnalyticsPost::query()
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        //->whereBetween('date', [$since, $until])
	        ->orderByDesc('created_time')
	        ->get()
	        ->keyBy('post_id');

	    $metrics = SocialAnalyticsPostInfo::query()
	        ->select('post_id', 'metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->where('social_network', 'youtube')
	        ->whereBetween('date', [$since, $until])
	        ->whereIn('metric', [
	            'views',
	            'likes',
	            'shares',
	            'comments',
	            'estimatedMinutesWatched',
	            'averageViewDuration',
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
	                                          ->map(fn($val) => round($val, 2))
	                                          ->toArray(),
	        ];
	    }

	    return $posts;
	}

	/*
	* SYNC DATA
	 */

    public function syncAccount(int $accountId, string $since, string $until)
	{
	    $since = Carbon::parse($since)->toDateString();
	    $until = Carbon::parse($until)->toDateString();

	    $account = Accounts::findOrFail($accountId);
	    $token = json_decode($account->token, true);
	    $this->client->setAccessToken($token);

	    $channelId = $account->pid;

	    // Skip if already synced
	    if (!\Analytics::shouldFetchSocialAnalytics($account->id, 'youtube', 'channel')) {
	        return true;
	    }

	    // Sync all relevant YouTube data
	    $this->syncYoutubeAnalyticsMetrics($accountId, $channelId, $since, $until);
	    $this->syncYoutubeSubscribersByCountry($accountId, $channelId, $since, $until);
	    $this->syncYoutubeViewsByCountry($accountId, $channelId, $since, $until);
	    $this->syncYoutubeViewsByTrafficSource($accountId, $channelId, $since, $until);
	    $this->syncYoutubeViewsByDevice($accountId, $channelId, $since, $until);
	    $this->syncYoutubeGenderDemographics($accountId, $channelId, $since, $until);
	    $this->syncYoutubeAgeGroupDemographics($accountId, $channelId, $since, $until);
	    $this->syncVideoInsights($accountId, $channelId, $since, $until);

	    // Mark as synced
	    \Analytics::markSynced($account->id, 'youtube', 'channel');

	    return true;
	}

	public function syncYoutubeAnalyticsMetrics(int $accountId, string $channelId, string $startDate, string $endDate, array $metrics = [], string $dimension = 'day')
	{
	    if (empty($metrics)) {
	        $metrics = ['views', 'likes', 'comments', 'shares', 'subscribersGained', 'subscribersLost', 'estimatedMinutesWatched', 'averageViewDuration'];
	    }

	    $metricsStr = implode(',', $metrics);

	    $data = $this->fetchYoutubeAnalyticsReport($channelId, $startDate, $endDate, [
	        'metrics'    => $metricsStr,
	        'dimensions' => $dimension,
	        'sort'       => $dimension === 'day' ? 'day' : ('-' . $metrics[0]),
	    ]);

	    foreach ($data as $row) {
	        $dimensionValue = $row[$dimension];

	        foreach ($metrics as $metric) {
	            if ($metric === 'subscribersGained' && isset($row['subscribersLost'])) {
	                $netSubscribers = ((int)($row['subscribersGained'] ?? 0)) - ((int)($row['subscribersLost'] ?? 0));
	                if ($netSubscribers > 0) {
	                    SocialAnalytics::updateOrCreate([
	                        'account_id' => $accountId,
	                        'metric'     => $dimension === 'day' ? 'subscribers' : ('subscribers.' . $dimensionValue),
	                        'date'       => $dimension === 'day' ? $dimensionValue : now()->toDateString(),
	                    ], [
	                        'value' => $netSubscribers,
	                    ]);
	                }
	            }

	            if (isset($row[$metric])) {
	                $value = (int) $row[$metric];
	                if ($value > 0) {
	                    $metricKey = ($metric === 'subscribersGained' || $metric === 'subscribersLost')
	                        ? $metric
	                        : $metric;
	                    SocialAnalytics::updateOrCreate([
	                        'account_id'     => $accountId,
	                        'social_network' => 'youtube',
	                        'metric'         => $dimension === 'day' ? $metricKey : ($metricKey . '.' . $dimensionValue),
	                        'date'           => $dimension === 'day' ? $dimensionValue : now()->toDateString(),
	                    ], [
	                        'value' => $value,
	                    ]);
	                }
	            }
	        }
	    }
	}

	public function syncYoutubeAgeGroupDemographics(int $accountId, string $channelId, string $startDate, string $endDate): void
	{
	    $params = [
	        'ids' => 'channel==' . $channelId,
	        'startDate' => $startDate,
	        'endDate' => $endDate,
	        'metrics' => 'viewerPercentage',
	        'dimensions' => 'ageGroup',
	        'sort' => '-viewerPercentage',
	        'maxResults' => 50,
	    ];

	    try {
	        $responseAge = $this->ytAnalytics->reports->query($params);
	        $rows = $responseAge->getRows() ?? [];
	    } catch (\Exception $e) {
	        logger()->error('[YouTubeAnalytics] syncYoutubeViewerDemographics error: ' . $e->getMessage());
	        return;
	    }

	    foreach ($rows as $row) {
	        [$ageGroup, $percentage] = $row;
	        $value = round((float) $percentage, 2);
	        if ($value > 0) {
	            $metricKey = "viewer_age.{$ageGroup}";
	            SocialAnalytics::updateOrInsert([
	                'account_id'     => $accountId,
	                'social_network' => 'youtube',
	                'metric'         => $metricKey,
	                'date'           => now()->toDateString(),
	            ], [
	                'value'   => $value,
	                'created' => time(),
	            ]);
	        }
	    }
	}

	public function syncYoutubeGenderDemographics(int $accountId, string $channelId, string $startDate, string $endDate): void
	{
	    $params = [
	        'ids' => 'channel==' . $channelId,
	        'startDate' => $startDate,
	        'endDate' => $endDate,
	        'metrics' => 'viewerPercentage',
	        'dimensions' => 'gender',
	        'sort' => '-viewerPercentage',
	        'maxResults' => 10,
	    ];

	    try {
	        $responseAge = $this->ytAnalytics->reports->query($params);
	        $rows = $responseAge->getRows() ?? [];
	    } catch (\Exception $e) {
	        logger()->error('[YouTubeAnalytics] syncYoutubeViewerDemographics error: ' . $e->getMessage());
	        return;
	    }

	    foreach ($rows as $row) {
	        [$gender, $percentage] = $row;
	        $value = round((float) $percentage, 2);
	        if ($value > 0) {
	            $metricKey = "viewer_gender.{$gender}";
	            SocialAnalytics::updateOrInsert([
	                'account_id'     => $accountId,
	                'social_network' => 'youtube',
	                'metric'         => $metricKey,
	                'date'           => now()->toDateString(),
	            ], [
	                'value'   => $value,
	                'created' => time(),
	            ]);
	        }
	    }
	}

    public function syncYoutubeViewsByCountry(int $accountId, string $channelId, string $startDate, string $endDate)
	{
	    $params = [
	        'ids'        => 'channel==' . $channelId,
	        'startDate'  => $startDate,
	        'endDate'    => $endDate,
	        'metrics'    => 'views',
	        'dimensions' => 'country',
	        'sort'       => '-views',
	        'maxResults' => 200,
	    ];
	    try {
	        $response = $this->ytAnalytics->reports->query($params);
	        $rows = $response->getRows() ?? [];
	    } catch (\Exception $e) {
	        logger()->error("[YoutubeAnalytics] syncYoutubeViewsByCountry error: " . $e->getMessage());
	        return;
	    }

	    foreach ($rows as $row) {
	        $countryCode = strtoupper($row[0]);
	        $views = (int) $row[1];
	        if ($views > 0 && $countryCode) {
	            SocialAnalytics::updateOrInsert([
	                'account_id'     => $accountId,
	                'social_network' => 'youtube',
	                'metric'         => 'views_country.' . $countryCode,
	                'date'           => now()->toDateString(),
	            ], [
	                'value'   => $views,
	                'created' => time(),
	            ]);
	        }
	    }
	}

	public function syncYoutubeSubscribersByCountry(int $accountId, string $channelId, string $startDate, string $endDate)
	{
	    $params = [
	        'ids'        => 'channel==' . $channelId,
	        'startDate'  => $startDate,
	        'endDate'    => $endDate,
	        'metrics'    => 'subscribersGained',
	        'dimensions' => 'country',
	        'sort'       => '-subscribersGained',
	        'maxResults' => 200,
	    ];
	    try {
	        $response = $this->ytAnalytics->reports->query($params);
	        $rows = $response->getRows() ?? [];

	    } catch (\Exception $e) {
	        logger()->error("[YoutubeAnalytics] syncYoutubeSubscribersByCountry error: " . $e->getMessage());
	        return;
	    }

	    foreach ($rows as $row) {
	        $countryCode = strtoupper($row[0]);
	        $gained = (int) $row[1];

	        if ($gained > 0 && $countryCode) {
	            SocialAnalytics::updateOrInsert([
	                'account_id'     => $accountId,
	                'social_network' => 'youtube',
	                'metric'         => 'subscribers_country.' . $countryCode,
	                'date'           => now()->toDateString(),
	            ], [
	                'value'   => $gained,
	                'created' => time(),
	            ]);
	        }
	    }
	}

	public function syncYoutubeViewsByTrafficSource(int $accountId, string $channelId, string $startDate, string $endDate)
	{
	    $params = [
	        'ids' => 'channel==' . $channelId,
	        'startDate' => $startDate,
	        'endDate' => $endDate,
	        'metrics' => 'views',
	        'dimensions' => 'insightTrafficSourceType',
	        'sort' => '-views',
	        'maxResults' => 50,
	    ];

	    try {
	        $response = $this->ytAnalytics->reports->query($params);
	        $rows = $response->getRows() ?? [];
	    } catch (\Exception $e) {
	        logger()->error("[YoutubeAnalytics] syncYoutubeViewsByTrafficSource error: " . $e->getMessage());
	        return;
	    }

	    foreach ($rows as $row) {
	        $sourceType = $row[0]; // e.g., "YT_SEARCH", "EXT_URL", "NO_LINK_EMBED"
	        $views = (int) $row[1];

	        if ($views > 0 && $sourceType) {
	            SocialAnalytics::updateOrInsert([
	                'account_id'     => $accountId,
	                'social_network' => 'youtube',
	                'metric'         => 'views_traffic_source.' . strtolower($sourceType),
	                'date'           => now()->toDateString(),
	            ], [
	                'value'   => $views,
	                'created' => time(),
	            ]);
	        }
	    }
	}

	public function syncYoutubeViewsByDevice(int $accountId, string $channelId, string $startDate, string $endDate)
	{
	    $params = [
	        'ids' => 'channel==' . $channelId,
	        'startDate' => $startDate,
	        'endDate' => $endDate,
	        'metrics' => 'views',
	        'dimensions' => 'deviceType',
	        'sort' => '-views',
	        'maxResults' => 50,
	    ];

	    try {
	        $response = $this->ytAnalytics->reports->query($params);
	        $rows = $response->getRows() ?? [];
	    } catch (\Exception $e) {
	        logger()->error("[YoutubeAnalytics] syncYoutubeViewsByDevice error: " . $e->getMessage());
	        return;
	    }

	    foreach ($rows as $row) {
	        $device = strtolower($row[0]); // E.g. DESKTOP, MOBILE, TABLET, GAME_CONSOLE, TV
	        $views = (int) $row[1];

	        if ($views > 0 && $device) {
	            SocialAnalytics::updateOrInsert([
	                'account_id'     => $accountId,
	                'social_network' => 'youtube',
	                'metric'         => 'views_device.' . $device,
	                'date'           => now()->toDateString(),
	            ], [
	                'value'   => $views,
	                'created' => time(),
	            ]);
	        }
	    }
	}

	public function syncVideoInsights(int $accountId, string $channelId, string $startDate, string $endDate): void
	{
	    $social = 'youtube';
	    $now = time();

	    try {
	        $videoIds = [];
	        $nextPageToken = null;
	        do {
	            $params = [
	                'channelId'      => $channelId,
	                'publishedAfter' => \Carbon\Carbon::parse($startDate)->startOfDay()->toRfc3339String(),
	                'publishedBefore'=> \Carbon\Carbon::parse($endDate)->endOfDay()->toRfc3339String(),
	                'maxResults'     => 50,
	                'type'           => 'video',
	                'order'          => 'date',
	                'pageToken'      => $nextPageToken,
	            ];
	            $response = $this->youtube->search->listSearch('id', $params);

	            foreach ($response->getItems() as $item) {
	                $videoId = $item->getId()->getVideoId();
	                if ($videoId) $videoIds[] = $videoId;
	            }

	            $nextPageToken = $response->getNextPageToken();
	        } while ($nextPageToken);

	        if (empty($videoIds)) return;

	        $chunks = array_chunk($videoIds, 50);
	        $metaInsert = [];
	        $metricsInsert = [];

	        foreach ($chunks as $chunk) {
	            $videos = $this->youtube->videos->listVideos('snippet,statistics', [
	                'id' => implode(',', $chunk)
	            ]);


	            foreach ($videos as $video) {
	                $videoId = $video->getId();
	                $snippet = $video->getSnippet();
	                $stats   = $video->getStatistics();
	                $createdAt = \Carbon\Carbon::parse($snippet->getPublishedAt());
	                $date = $createdAt->toDateString();

	                $metaInsert[] = [
	                    'account_id'     => $accountId,
	                    'social_network' => $social,
	                    'post_id'        => $videoId,
	                    'date'           => $date,
	                    'message'        => $snippet->getTitle(),
	                    'created_time'   => $createdAt->toDateTimeString(),
	                    'full_picture'   => $snippet->getThumbnails()?->getMedium()?->getUrl(),
	                    'permalink_url'  => 'https://www.youtube.com/watch?v=' . $videoId,
	                    'type'           => 'video',
	                    'status_type'    => null,
	                    'created'        => $now,
	                ];

	                foreach ([
	                    'views'    => (int) $stats->getViewCount(),
	                    'likes'    => (int) $stats->getLikeCount(),
	                    'comments' => (int) $stats->getCommentCount(),
	                ] as $metric => $value) {
	                    $metricsInsert[] = [
	                        'post_id'        => $videoId,
	                        'account_id'     => $accountId,
	                        'social_network' => $social,
	                        'metric'         => $metric,
	                        'value'          => $value,
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
	            \Analytics::markSynced($accountId, $social, 'video');
	        }
	    } catch (\Exception $e) {
	        logger()->error("[YoutubeAnalytics] syncVideoInsights error: " . $e->getMessage());
	    }
	}

	public function fetchYoutubeAnalyticsReport(string $channelId, string $startDate, string $endDate, array $options = [])
	{
	    $params = [
	        'ids' => 'channel==' . $channelId,
	        'startDate' => $startDate,
	        'endDate' => $endDate,
	        'metrics' => $options['metrics'] ?? 'views,likes,comments,shares',
	        'dimensions' => $options['dimensions'] ?? 'day',
	        'sort' => $options['sort'] ?? 'day',
	        'maxResults' => $options['maxResults'] ?? 200,
	    ];
	    if (!empty($options['filters'])) {
	        $params['filters'] = $options['filters'];
	    }
	    try {
	        $response = $this->ytAnalytics->reports->query($params);

	        $results = [];
	        if ($response->getRows()) {
	            $headers = array_map(fn($h) => $h->getName(), $response->getColumnHeaders());
	            foreach ($response->getRows() as $row) {
	                $results[] = array_combine($headers, $row);
	            }
	        }
	        return $results;

	    } catch (\Exception $e) {
	        logger()->error('YouTube Analytics Error: ' . $e->getMessage(), [
	            'params' => $params,
	            'trace' => $e->getTraceAsString()
	        ]);
	        return [];
	    }
	}
		   

}
