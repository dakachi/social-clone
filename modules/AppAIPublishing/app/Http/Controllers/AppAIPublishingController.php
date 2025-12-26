<?php

namespace Modules\AppAIPublishing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppAIPublishing\Models\AIPosts;
use Modules\AppFiles\Models\Files;
use Channels;
use Arr;
use DB;

class AppAIPublishingController extends Controller
{

    public function index()
    {
        $total = AIPosts::where("team_id", request()->team_id)->count();
        return view('appaipublishing::index',[
            "total" => $total
        ]);
    }

    public function list(Request $request)
    {
        $result = AIPosts::getAIPostsList([
            "keyword" => $request->keyword,
            "page" => (int)$request->input("page") + 1,
            "length" => 24,
        ]);

        return ms([
            "status" => 1,
            "data" => view( module("key") . '::list', [
                "result" => $result
            ])->render()
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $result = AIPosts::where("id_secure", $id)->where("team_id", $request->team_id)->first();
        $folders = Files::where("team_id", $request->team_id)->where("is_folder", 1)->get();
        return view( module("key") . '::update', [
            'result' => $result,
            'folders' => $folders,
        ]);
    }

    public function save(Request $request)
    {
        $time_now = time();
        $team_id = $request->team_id;

        // Retrieve post data using Laravel's request object
        $id_secure       = (string)$request->input('id');
        $name            = (string)$request->input('name');
        $account_ids     = (array)$request->input('accounts');
        $prompt_ids      = (array)$request->input('prompts');
        $options         = (array)$request->input('options');
        $weekdays        = (array)$request->input('weekdays');
        $ai_options      = (array)$request->input('ai_options');
        $end_date_input  = (string)$request->input('end_date');
        $time_posts      = (array)$request->input('time_posts');

        // Convert the end_date string to a Unix timestamp using your helper
        $end_date = (int) timestamp_sql($end_date_input);

        // Validation (you can also use Laravel validation rules here)
        if (empty($name)) {
            return response()->json([
                "status" => 0,
                "message" => __('Campaign name is required')
            ]);
        }

        if (empty($account_ids)) {
            return response()->json([
                "status" => 0,
                "message" => __('Please select at least a profile')
            ]);
        }

        if (empty($prompt_ids)) {
            return response()->json([
                "status" => 0,
                "message" => __('Please add at least a new prompt')
            ]);
        }

        if (empty($weekdays)) {
            return response()->json([
                "status" => 0,
                "message" => __('Please enter at least a day of the week')
            ]);
        }

        if (empty($time_posts)) {
            return response()->json([
                "status" => 0,
                "message" => __('Please enter at least a time')
            ]);
        }

        if (empty($end_date)) {
            return response()->json([
                "status" => 0,
                "message" => __('Please enter at least an end date')
            ]);
        }

        // Retrieve the list of channels
        $list_accounts = Channels::list($account_ids);

        if (empty($list_accounts)) {
            return response()->json([
                "status" => 0,
                "message" => __('Accounts selected is inactive. Let re-login and try again')
            ]);
        }

        // Collect active channels IDs
        $accounts = [];
        foreach ($list_accounts as $account) {
            $accounts[] = $account->id;
        }

        // Clean prompts array
        $prompt_ids = !empty($prompt_ids) ? array_filter($prompt_ids) : [];
        $list_prompts = DB::table("ai_prompts")->whereIn("id_secure", $prompt_ids)->get();
        $prompts = $list_prompts->pluck('id')->toArray();

        // Process weekdays input using Arr::get for safely retrieving values
        $weekdays_array = [
            "Mon" => (int) Arr::get($weekdays, "Mon", 0),
            "Tue" => (int) Arr::get($weekdays, "Tue", 0),
            "Wed" => (int) Arr::get($weekdays, "Wed", 0),
            "Thu" => (int) Arr::get($weekdays, "Thu", 0),
            "Fri" => (int) Arr::get($weekdays, "Fri", 0),
            "Sat" => (int) Arr::get($weekdays, "Sat", 0),
            "Sun" => (int) Arr::get($weekdays, "Sun", 0),
        ];

        // Prepare the post data options
        $postData = [
            "include_media"     => isset($ai_options['include_media']) ? $ai_options['include_media'] : 0,
            "hashtags"          => isset($ai_options['hashtags']) ? (int)$ai_options['hashtags'] : 0,
            "max_length"        => isset($ai_options['max_length']) ? (int)$ai_options['max_length'] : 35,
            "tone_of_voice"     => isset($ai_options['tone_of_voice']) ? $ai_options['tone_of_voice'] : "Polite",
            "language"          => isset($ai_options['language']) ? $ai_options['language'] : "en-US",
            "creativity"        => isset($ai_options['creativity']) ? $ai_options['creativity'] : "Good",
            "time_posts"        => $time_posts,
            "weekdays"          => $weekdays_array,
            "end_date"          => $end_date,
            "options"           => $options,
        ];

        // Calculate the next posting time with your helper function
        $next_time = $this->getNextTime($time_posts, $weekdays_array);

        // Check that the next time is within the end date
        if ($next_time > $end_date) {
            return response()->json([
                "status"  => 0,
                "message" => __('The end time is invalid')
            ]);
        }

        // Prepare data to be inserted/updated
        $data = [
            "team_id"  => $team_id,
            "accounts" => json_encode($accounts),
            "name"     => $name,
            "prompts"  => json_encode($prompts),
            "data"     => json_encode($postData),
            "end_date" => ($end_date == 0 ? null : $end_date),
            "changed"  => time(),
            "time_post" => $next_time,
        ];

        $item = DB::table("ai_posts")
                    ->where('id_secure', $id_secure)
                    ->where('team_id', $team_id)
                    ->first();

        if ($item) {
            // Update existing record
            DB::table("ai_posts")
              ->where('id', $item->id)
              ->update($data);
        } else {
            // Insert a new record
            $data['id_secure'] = rand_string();
            $data['status'] = 1;
            $data['created'] = time();
            DB::table("ai_posts")->insert($data);
        }

        return response()->json([
            "status"  => 1,
            "message" => __('Success')
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
            $expiredSchedules = AIPosts::whereIn('id_secure', $id_arr)
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

        AIPosts::whereIn('id_secure', $id_arr)
            ->update(['status' => $updateStatus]);

        return ms([
            "status" => 1,
            "message" => __('Succeed')
        ]);
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy(AIPosts::class, $request->input('id'));
        return response()->json($response);
    }
}
