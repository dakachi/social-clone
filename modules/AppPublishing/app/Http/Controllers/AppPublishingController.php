<?php

namespace Modules\AppPublishing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppPublishing\Models\Posts;
use Validator;
use Channels;
use Publishing;
use DB;
use Arr;
use Media;

class AppPublishingController extends Controller
{
    public function index(Request $request)
    {

        $campaigns = DB::table("post_campaigns")->where("team_id", $request->team_id)->get();
        $labels = DB::table("post_labels")->where("team_id", $request->team_id)->get();

        return view(module("key") . '::index', [
            "campaigns" => $campaigns,
            "labels" => $labels,
        ]);
    }

    /**
     * Retrieve calendar events from the database with dynamic filters.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function events(Request $request)
    {
        $teamId = $request->team_id;

        // Build the query from the Posts model
        $query = Posts::with('account');

        // Filter by team_id
        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        // Filter by date range (time_post)
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('time_post', [
                strtotime($request->start), 
                strtotime($request->end)
            ]);
        }

        $query->where('status', '!=', 1);

        // Dynamic filter by status
        if ($request->filled('status') && $request->status !== '-1') {
            $query->where('status', $request->status);
        }

        // Dynamic filter by module_name (social network)
        if ($request->filled('module_name')) {
            $query->where('module', $request->module_name);
        }

        // Dynamic filter by campaign
        if ($request->filled('campaign')) {
            $query->where('campaign', $request->campaign);
        }

        // Dynamic filter by label
        if ($request->filled('label')) {
            $labels = is_array($request->label) ? $request->label : [$request->label];
            $query->where(function($q) use ($labels) {
                foreach ($labels as $label) {
                    $q->orWhereJsonContains('labels', (int)$label);
                }
            });
        }

        $query->orderBy('time_post', 'DESC');

        // Get the list of posts
        $posts = $query->get();

        // Transform posts into FullCalendar event objects.
        $events = $posts->map(function($post) {
            $postData = json_decode($post->data, true);
            $moduleInfo = \Module::find($post->module);

            $module = [];
            if ($moduleInfo) {
                $menu = $moduleInfo->get('menu');
                $module = [
                    'icon' => $menu['icon'],
                    'color' => $menu['color'],
                    'name' => $menu['name'],
                ];
            }

            switch ($post->type) {
                case 'text':  $type = 1; break;
                case 'link':  $type = 2; break;
                case 'media': $type = 3; break;
                default:      $type = 4; break;
            }

            $medias = $postData['medias'] ?? [];
            $media = !empty($medias) ? Media::url($medias[0]) : '';

            return [
                'title'           => $postData['caption'] ?? 'No Title',
                'start' => date('Y-m-d\TH:i:s', $post->time_post),
                'backgroundColor' => '000',
                'borderColor'     => '000',
                'textColor'       => $module['color'] ?? '',
                'className'       => '',
                'extendedProps'   => [
                    'id'           => $post->id_secure,
                    'status'       => $post->status,
                    'type'         => $type,
                    'icon'         => $module['icon'] ?? '',
                    'color'        => $module['color'] ?? '',
                    'account_name' => $post->account->name ?? ($postData['account_name'] ?? ''),
                    'image'        => $media,
                    'caption'      => $postData['caption'] ?? '',
                    'link'      => $postData['link'] ?? '',
                    'time_post'    => date('h:i A', $post->time_post),
                    'module_name'  => $module['name'] ?? '',
                    'response'     => json_decode($post->result ?? '{}'),
                ],
            ];
        });

        // Return the events as JSON data with a 'data' key.
        return response()->json(['data' => $events]);
    }

    public function preview(Request $request){
        $id = $request->id;

        $post = Posts::with('account')->where("id_secure", $id)->where("team_id", $request->team_id)->first();

        ms([
            "status" => 1,
            "data" => view(module("key") . '::preview', [
                "post" => $post
            ])->render()
        ]);
    }

    public function changePostDate(Request $request)
    {
        $newDate = $request->new_date;
        $id = $request->id;

        $post = Posts::where("id_secure", $id)->where("team_id", $request->team_id)->first();

        if(!$post){
            ms([
                "status" => 0,
                "message" => __("The post does not exist or has been deleted.")
            ]);
        }

        $newTimePost = changeDateKeepTime($newDate, $post->time_post);

        Posts::where("id", $post->id)->update([
            "time_post" => $newTimePost,
            "changed" => time()
        ]);

        ms([ "status" => 1 ]);
    }

    public function composer(Request $request)
    {
        $id = $request->input("id");
        $date = $request->input("date");
        $post = Posts::where("id_secure", $id)->first();

        $labels = DB::table('post_labels')
            ->where("team_id", $request->team_id)
            ->where("status", 1)
            ->get();

        $campaigns = DB::table('post_campaigns')
            ->where("team_id", $request->team_id)
            ->where("status", 1)
            ->get();

        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::composer', [
                "labels"    => $labels,
                "campaigns" => $campaigns,
                "post"      => $post,
                "date"      => $date,
            ])->render()
        ]);
    }

    public function getLinkInfo(Request $request){
        $url = $request->input('value');

        $linkInfo = getLinkInfo($url);

        return response()->json([
            "status" => 1,
            "data"   => $linkInfo
        ]);
    }

    public function save(Request $request)
    {
        $skipValidate    = (bool) $request->confirm;
        $type            = (string) $request->type;
        $postBy          = (int) $request->post_by;
        $caption         = (string) $request->caption;
        $timePosts       = (array) $request->time_posts;
        $link            = (string) $request->link;
        $medias          = (array) $request->medias;
        $options         = (array) $request->options;
        $campaignIdSecure = (string) $request->campaign;
        $labelIds        = (array) $request->labels;

        $currentTime     = time();
        $timePost        = (int) timestamp_sql($request->time_post);
        $intervalPerPost = $request->interval_per_post;
        $repostFrequency = $request->repost_frequency;
        $repostUntil     = isset($request->repost_until) ? timestamp_sql($request->repost_until) : null;
        $listData        = [];

        $quota = Publishing::checkQuota($request->team_id);
        if (!$quota['can_post']) {
            return ms([
                "status"  => 0,
                "message" => $quota['message']
            ]);
        }

        $channels = Channels::list($request->accounts);
        if (!$channels) {
            return ms([
                "status"  => 0,
                "message" => __("Please select at least a channel")
            ]);
        }

        $rules = [
            'type'   => 'required|string',
        ];
        $messages = [
            'type.required' => __('Type is required'),
        ];

        switch ($type) {
            case "media":
                $rules['medias'] = 'required|array|min:1';
                $messages['medias.required'] = __('Please select at least one media');
                break;
            case "link":
                $rules['link'] = 'required|url';
                $messages['link.required'] = __('Link is required');
                $messages['link.url']      = __('Link must be a valid URL');
                break;
            default:
                $rules['caption'] = 'required|string';
                $messages['caption.required'] = __('Caption is required');
                $type = "text";
                break;
        }

        if ($postBy === 2) {
            $rules['time_post']         = 'required';
            $rules['repost_frequency']  = 'required';
            $rules['interval_per_post'] = 'required';
            $messages['time_post.required']         = __('Time post is required');
            $messages['repost_frequency.required']  = __('Repost frequency is required');
            $messages['interval_per_post.required'] = __('Interval per post is required');
        } elseif ($postBy === 3) {
            $rules['time_posts'] = 'required|array|min:1';
            $messages['time_posts.required'] = __('Please select at least a time post');
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ms([
                "status"  => 0,
                "message" => $validator->errors()->first()
            ]);
        }

        if ($postBy === 2) {
            if ($timePost <= $currentTime) {
                return ms([
                    "status"  => "error",
                    "message" => __("Time post must be greater than current time")
                ]);
            }

            if ($repostFrequency > 0) {
                if (!$repostUntil) {
                    return ms([
                        "status"  => "error",
                        "message" => __("Repost until is required")
                    ]);
                }
                if ($timePost > $repostUntil) {
                    return ms([
                        "status"  => "error",
                        "message" => __("Time post must be smaller than repost until")
                    ]);
                }
            }
        }

        $campaign = DB::table("post_campaigns")
            ->where(["id_secure" => $campaignIdSecure, "team_id" => $request->team_id])
            ->first();
        $campaignId = $campaign ? $campaign->id : 0;

        $labels = DB::table("post_labels")
            ->whereIn("id_secure", $labelIds)
            ->where(["team_id" => $request->team_id])
            ->get();
        $labelIdsArray = $labels ? Arr::pluck($labels, 'id') : [];

        $postData = [
            "caption" => $caption,
            "link"    => $link,
            "medias"  => $medias,
            "options" => $options,
        ];

        $data = [
            "campaign"         => $campaignId,
            "labels"           => $labelIdsArray,
            "team_id"          => $request->team_id,
            "function"         => "post",
            "type"             => $type,
            "data"             => json_encode($postData),
            "time_post"        => 0,
            "delay"            => $intervalPerPost,
            "repost_frequency" => $repostFrequency,
            "repost_until"     => ($repostFrequency == 0) ? null : $repostUntil,
            "result"           => "",
            "status"           => 3,
            "changed"          => $currentTime,
            "created"          => $currentTime,
        ];

        if ($postBy === 2) {
            $data['time_post'] = $timePost;
        } elseif ($postBy === 3) {
            $timePosts = array_unique(array_filter($timePosts));
            $data['repost_frequency'] = 0;
            $data['repost_until']     = null;
            $data['delay']            = 0;
        } elseif ($postBy === 4) {
            $data['status']      = 1;
            $data['delay']       = 5;
            $data['time_post']   = null;
            $data['repost_until'] = null;
        } else {
            $data['time_post'] = $currentTime;
        }

        foreach ($channels as $key => $channel) {
            $postId = $request->post_id ? $request->post_id : rand_string();
            $data['id_secure']      = $postId;
            $data['account_id']     = $channel->id;
            $data['social_network'] = $channel->social_network;
            $data['category']       = $channel->category;
            $data['api_type']       = $channel->login_type;
            $data['module']         = $channel->module;

            if ($postBy === 3) {
                foreach ($timePosts as $time) {
                    $data['time_post'] = (int)timestamp_sql($time);
                    $listData[] = (object)$data;
                }
            } elseif ($postBy === 2) {
                $data['time_post'] = $timePost + ($intervalPerPost * $key * 60);
                $listData[] = (object)$data;
            } else {
                $listData[] = (object)$data;
            }
        }

        $validatorResult = Publishing::validate($listData);
        $socialCanPost   = json_decode($validatorResult["can_post"]);

        if (($skipValidate && !empty($socialCanPost)) || $validatorResult["status"] == 1) {
            $result = Publishing::post($listData, $socialCanPost);
            return response()->json($result);
        }

        return response()->json($validatorResult);
    }

    public function destroyByFilter(Request $request)
    {
        $query = Posts::query();
        $query->where('team_id', $request->team_id);

        if ($request->filled('status') && $request->status !== '-1') {
            $query->where('status', $request->status);
        }

        if ($request->filled('module_name')) {
            $query->where('module', $request->module_name);
        }

        if ($request->filled('campaign')) {
            $query->where('campaign', $request->campaign);
        }

        if ($request->filled('label')) {
            $labels = is_array($request->label) ? $request->label : [$request->label];
            $query->where(function($q) use ($labels) {
                foreach ($labels as $label) {
                    $q->orWhereJsonContains('labels', (int)$label);
                }
            });
        }

        $postIds = $query->pluck('id')->toArray();

        $deleted = $query->delete();

        return response()->json([
            'status' => 1,
            'deleted' => $deleted,
            'post_ids' => $postIds,
            'message' => __("Deleted :count posts.", ['count' => $deleted])
        ]);
    }

    public function destroy(Request $request)
    {
        $response = \DBHelper::destroy(Posts::class, $request->input('id'));
        return response()->json($response);
    }
}
