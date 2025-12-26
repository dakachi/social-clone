<?php

namespace Modules\AppRssSchedules\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Modules\AppChannels\Models\Accounts;
use Illuminate\Pagination\Paginator;
use Modules\AppRssSchedules\Models\RssSchedule;
use Modules\AppPublishing\Models\PostStat;
use Arr;

class AppRssSchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view(module('key').'::index');
    }

    public function list(Request $request)
    {
        $search = $request->input('keyword');
        $status = $request->input('status');
        $current_page = (int) $request->input('page', 0) + 1;
        $per_page = 10;

        $teamId = $request->input('team_id') ?? (auth()->user()->team_id ?? null);

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        $query = RssSchedule::where('team_id', $teamId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('url', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('desc', 'like', '%' . $search . '%');
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        $schedules = $query->orderByDesc('changed')->paginate($per_page);

        if ($schedules->total() == 0 && $current_page > 1) {
            return response()->json([
                "status" => 0,
                "message" => __("No data found."),
            ]);
        }

        $rss_ids = $schedules->pluck('id')->toArray();

        $post_stats = PostStat::where('method', 'rss')
            ->whereIn('query_id', $rss_ids)
            ->whereIn('status', [4, 5]) // 4: success, 5: failed
            ->selectRaw('query_id, status, COUNT(*) as total')
            ->groupBy('query_id', 'status')
            ->get();

        $rss_stats = [];
        foreach ($rss_ids as $rss_id) {
            $rss_stats[$rss_id] = ['success' => 0, 'failed' => 0];
        }
        foreach ($post_stats as $row) {
            if (!isset($rss_stats[$row->query_id])) continue;
            if ($row->status == 4) $rss_stats[$row->query_id]['success'] = $row->total;
            if ($row->status == 5) $rss_stats[$row->query_id]['failed'] = $row->total;
        }

        foreach ($schedules as $item) {
            $rss_id = $item->id;
            $item->success = $rss_stats[$rss_id]['success'] ?? 0;
            $item->failed  = $rss_stats[$rss_id]['failed'] ?? 0;
        }

        return response()->json([
            "status" => 1,
            "data" => view(module('key') . '::list', [
                "schedules" => $schedules
            ])->render()
        ]);
    }



    public function create()
    {
        return view(module('key').'::update', [
            'result' => false
        ]);
    }

    public function edit($id_secure = "")
    {
        $result = RssSchedule::where('id_secure', $id_secure)->first();
        if (!$result) {
            return redirect(module_url());
        }

        $accounts = is_array($result->accounts) ? $result->accounts : json_decode($result->accounts, true);
        $data = is_array($result->data) ? $result->data : json_decode($result->data, true);

        return view(module('key') . '::update', [
            'result' => $result,
            'accounts' => $accounts ?: [],
            'data' => $data ?: [],
        ]);
    }

    public function save(Request $request)
    {
        $time_now = time();
        $team_id = $request->input('team_id');
        $id_secure = (string) $request->input('id_secure');

        $url         = trim($request->input('url'));
        $title       = trim($request->input('title'));
        $desc        = trim($request->input('desc'));
        $accounts    = (array) $request->input('accounts');
        $status      = (int) $request->input('status', 1);

        // Parse start_date and end_date
        $start_date_input = (string)$request->input('start_date');
        $start_date = !empty($start_date_input) ? (int)timestamp_sql($start_date_input) : null;

        $end_date_input = (string)$request->input('end_date');
        $time_end = $end_date = !empty($end_date_input) ? (int)timestamp_sql($end_date_input) : null;

        // Schedule config
        $time_posts = (array) $request->input('time_posts', []);
        $weekdays   = (array) $request->input('weekdays', []);

        // Other options
        $url_shorten      = $request->input('url_shorten', 0);
        $deny_link        = $request->input('deny_link', 0);
        $accept_caption   = $request->input('accept_caption', 1);
        $referral_code    = $request->input('referral_code');
        $include_keywords = $request->input('include_keywords', '');
        $ignore_keywords  = $request->input('ignore_keywords', '');

        // Validation
        if (empty($url)) {
            return response()->json(["status" => 0, "message" => __('RSS URL is required')]);
        }
        if (empty($accounts)) {
            return response()->json(["status" => 0, "message" => __('Please select at least one account')]);
        }
        if (empty($time_posts)) {
            return response()->json(["status" => 0, "message" => __('Please enter at least a time')]);
        }
        if (empty($weekdays)) {
            return response()->json(["status" => 0, "message" => __('Please enter at least a day of the week')]);
        }
        if (empty($end_date)) {
            return response()->json(["status" => 0, "message" => __('Please enter an end date')]);
        }

        // Validate RSS feed using Facade
        try {
            $rssData = \RssAutomation::fetchRSS($url);
            $feedTitle = $rssData['feed_title'] ?? '';
            $feedDesc  = $rssData['feed_description'] ?? '';
        } catch (\Exception $e) {
            return response()->json([
                "status" => 0,
                "message" => __('The RSS URL could not be fetched or is not a valid RSS feed.') . ' ' . $e->getMessage(),
            ]);
        }

        // Validate that all selected accounts are active
        $list_accounts = Accounts::whereIn('id_secure', $accounts)
            ->where('team_id', $team_id)
            ->where('status', 1)
            ->get();
        if ($list_accounts->isEmpty()) {
            return response()->json([
                "status" => 0,
                "message" => __('Accounts selected are inactive. Please re-login and try again.')
            ]);
        }
        $account_ids = $list_accounts->pluck('id')->toArray();

        // Process weekdays
        $weekdays_array = [
            "Mon" => (int)Arr::get($weekdays, "Mon", 0),
            "Tue" => (int)Arr::get($weekdays, "Tue", 0),
            "Wed" => (int)Arr::get($weekdays, "Wed", 0),
            "Thu" => (int)Arr::get($weekdays, "Thu", 0),
            "Fri" => (int)Arr::get($weekdays, "Fri", 0),
            "Sat" => (int)Arr::get($weekdays, "Sat", 0),
            "Sun" => (int)Arr::get($weekdays, "Sun", 0),
        ];

        // Collect all config and schedule info into dataField
        $dataField = [
            'url_shorten'      => $url_shorten,
            'deny_link'        => $deny_link,
            'accept_caption'   => $accept_caption,
            'referral_code'    => $referral_code,
            'include_keywords' => $include_keywords,
            'ignore_keywords'  => $ignore_keywords,
            'time_posts'       => $time_posts,
            'weekdays'         => $weekdays_array,
            'start_date'       => $start_date,
            'end_date'         => $end_date,
        ];

        // Calculate next_try
        $next_try = $this->getNextTime($time_posts, $weekdays_array);

        if ($next_try > $end_date) {
            return response()->json([
                "status"  => 0,
                "message" => __('The end time is invalid')
            ]);
        }
        // Ensure next_try >= start_date if set
        if ($start_date && $next_try < $start_date) {
            $next_try = $start_date;
        }

        // Check for duplicate RSS schedules for these accounts
        $accountsJson = json_encode($account_ids);
        $query = RssSchedule::where('team_id', $team_id)
            ->where('url', $url)
            ->where('accounts', $accountsJson);

        if (!empty($id_secure)) {
            $query->where('id_secure', '!=', $id_secure);
        }

        if ($query->exists()) {
            return response()->json([
                'status' => 0,
                'message' => __('This RSS feed is already scheduled for these accounts.')
            ]);
        }

        // Prepare data for insert or update
        $data = [
            "team_id"    => $team_id,
            "accounts"   => json_encode($account_ids),
            "url"        => $url,
            "title"      => $title ?: $feedTitle,
            "desc"       => $desc ?: $feedDesc,
            "data"       => json_encode($dataField),
            "start_date" => $start_date ?? $time_now,
            "end_date"   => $end_date,
            "time_post"  => $next_try,
            "next_try"   => $next_try,
            "status"     => $status,
            "changed"    => $time_now,
        ];

        // Insert or update record using Eloquent Model
        if (!empty($id_secure)) {
            $item = RssSchedule::where('id_secure', $id_secure)
                ->where('team_id', $team_id)
                ->first();
            if ($item) {
                $item->fill($data);
                $item->save();
            } else {
                $data['id_secure'] = rand_string();
                $data['created'] = $time_now;
                RssSchedule::create($data);
            }
        } else {
            $data['id_secure'] = rand_string();
            $data['created'] = $time_now;
            RssSchedule::create($data);
        }

        return response()->json([
            "status"  => 1,
            "message" => __('RSS schedule has been saved successfully!')
        ]);
    }

    public function status(Request $request, $status = "pause")
    {
        $ids = $request->input('id');
        $id_arr = [];

        if (empty($ids)) {
            return ms([
                "status" => 0,
                "message" => __("Please select at least one item"),
            ]);
        }

        if (is_string($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $value) {
            $id_key = $value;
            if ($id_key != 0) {
                $id_arr[] = $id_key;
            }
        }

        if ($status === 'start') {
            $now = time();
            $expiredSchedules = RssSchedule::whereIn('id_secure', $id_arr)
                ->where('end_date', '<=', $now)
                ->pluck('id_secure')
                ->toArray();

            if (!empty($expiredSchedules)) {
                return ms([
                    "status" => 0,
                    "message" => __("Cannot start expired schedules. Please update the end date to reactivate these schedules."),
                    "expired_ids" => $expiredSchedules
                ]);
            }
            $updateStatus = 1;
        } else {
            $updateStatus = 0;
        }

        RssSchedule::whereIn('id_secure', $id_arr)
            ->update(['status' => $updateStatus]);

        return ms([
            "status" => 1,
            "message" => __('Succeed')
        ]);
    }

    private function getNextTime(array $timePosts, array $weekdays): int
    {
        $timeNow = time();

        if (!empty($timePosts)) {
            usort($timePosts, fn($a, $b) => strtotime($a) <=> strtotime($b));
        }

        $currentDay = strtotime(date("Y-m-d"));
        $nextTime = $timeNow;

        for ($i = 0; $i < 7; $i++) {
            $nextDay = $currentDay + (86400 * $i);
            $day = date("D", $nextDay);

            if (!empty($weekdays[$day])) {
                foreach ($timePosts as $timePost) {
                    $timePost24 = date("G:i", strtotime($timePost));
                    [$hours, $minutes] = explode(':', $timePost24);

                    $timeSeconds = $nextDay + ($hours * 3600) + ($minutes * 60);

                    if ($timeSeconds > $timeNow) {
                        return $timeSeconds;
                    }
                }
            }
        }

        return $nextTime;
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy(RssSchedule::class, $request->input('id'));
        return response()->json($response);
    }
}
