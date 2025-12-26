<?php

namespace Modules\AppAIPublishing\Console;

use Illuminate\Console\Command;
use Modules\AppAIPublishing\Models\AIPosts;
use Modules\AppAIPrompts\Models\AIPrompt;
use Modules\AppChannels\Models\Accounts;
use Modules\AdminUsers\Models\Teams;
use App\Models\User;
use AI;
use Publishing;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;


class CronJobCommand extends Command
{
    // The name and signature of the console command.
    protected $signature = 'appaipublishing:cron';

    // The console command description.
    protected $description = 'Execute the scheduler cron job';

    public function handle()
    {
        \Log::info("CronJobCommand executed at " . now());

        try {
            $posts = AIPosts::getAIPosts(5);

            if (!$posts) {
                echo "Empty schedule";
                return;
            }

            foreach ($posts as $post) {
                try {
                    $data = $post->data ?? [];
                    if (is_object($data)) $data = (array) $data;  
                    $timePosts = (array) ($data['time_posts'] ?? []);
                    $weekdays  = (array) ($data['weekdays'] ?? []);
                    $options   = (array) ($data['options'] ?? []);

                    $accounts = $post->accounts ?? [];
                    if (empty($accounts)) {
                        $this->logAndStop($post, "All accounts require re-login.");
                        continue;
                    }

                    if (empty($post->prompts)) {
                        $this->logAndStop($post, "Prompts are required to start.");
                        continue;
                    }

                    $team = Teams::find($post->team_id);
                    $userTimezone = $team?->owner ? User::find($team->owner)?->timezone : config('app.timezone', 'UTC');

                    $nextTime = $this->getNextTime($timePosts, $weekdays, $userTimezone);
                    $status   = $nextTime > $post->end_date ? 2 : 1;

                    $this->updatePostSchedule($post->id, $nextTime, $status);
                    $this->updateNextTry($post->id);

                    $prompt  = $this->getRandomPrompt($post->prompts);
                    $caption = $this->generateCaption($prompt, (object) $data, $post->team_id);
                    $title   = $this->generateTitle($caption, (object) $data, $post->team_id);

                    $this->updateOptions($options, $title);
                    $medias = $this->fetchImages($data['include_media'] ?? null, $caption, $post->team_id);

                    $postData = $this->preparePostData($accounts, $post->id, $caption, $medias, $options);

                    if (!empty($postData)) {
                        $this->handlePosting($post, $postData);
                    }

                } catch (\Throwable $e) {
                    dd($e);
                    \Log::error("Error processing post ID {$post->id}: " . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    //$this->logAndStop($post, $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Log::error("CronJobCommand failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return 0;
    }

    /**
     * Tính thời gian post tiếp theo dựa vào danh sách giờ & ngày trong tuần
     */
    private function getNextTime(array $timePosts, array $weekdays, string $timezone = 'UTC'): int
    {
        $now = Carbon::now($timezone);

        if (empty($timePosts) || empty($weekdays)) {
            return $now->timestamp;
        }

        // Chuẩn hoá timePosts
        $timePosts = array_map(fn($t) => date("H:i", strtotime($t)), $timePosts);
        sort($timePosts);

        // Ép weekdays về int (0/1)
        $weekdays = array_map('intval', $weekdays); 
        // ex: ["Mon" => 0, "Tue" => 0, "Wed" => 1, ...]

        // Duyệt 14 ngày tới
        for ($i = 0; $i < 14; $i++) {
            $day = $now->copy()->startOfDay()->addDays($i);
            $dayName = $day->format('D'); // "Mon", "Tue", ...

            if (!empty($weekdays[$dayName])) { // chỉ lấy ngày có bật
                foreach ($timePosts as $tp) {
                    [$h, $m] = explode(':', $tp);
                    $slot = $day->copy()->setTime((int)$h, (int)$m);

                    if ($slot->greaterThan($now)) {
                        // trả về UTC timestamp để lưu DB
                        return $slot->clone()->setTimezone('UTC')->timestamp;
                    }
                }
            }
        }

        // fallback: sau 5 phút nữa
        return $now->addMinutes(3600)->setTimezone('UTC')->timestamp;
    }

    /**
     * Helper function to update post schedule in DB
     */
    private function updatePostSchedule($postId, $time, $status)
    {
        AIPosts::where('id', $postId)->update([
            "time_post" => $time,
            "status" => $status
        ]);
    }

    /**
     * Helper function to update the next retry time
     */
    private function updateNextTry($postId)
    {
        AIPosts::where('id', $postId)->update([
            "next_try" => time() + 120
        ]);
    }

    /**
     * Get a random prompt from the list
     */
    private function getRandomPrompt(array $prompts)
    {
        if (empty($prompts)) {
            throw new \Exception("No prompts available to select");
        }

        $promptId = $prompts[array_rand($prompts)];

        $prompt = AIPrompt::find($promptId);

        if (!$prompt) {
            throw new \Exception("Unable to generate caption");
        }

        return $prompt->prompt;
    }

    /**
     * Generate caption using AI
     */
    private function generateCaption(string $keyword, object $data, int $teamId)
    {
        // Map creativity to human-readable instruction
        $creativityMap = [
            'low'    => 'Keep the caption simple, straightforward, and factual.',
            'medium' => 'Balance clarity with a touch of creativity.',
            'high'   => 'Be imaginative, playful, and use metaphors if suitable.'
        ];
        $creativityInstruction = $creativityMap[strtolower($data->creativity)] ?? "Be clear and engaging.";

        // Build prompt with detailed, natural instructions
        $instructions = [
            "Write a social media caption about: \"{$keyword}\".",
            "Do not exceed {$data->max_length} words.",
            $creativityInstruction,
            "Write in {$data->language}.",
            "Use a {$data->tone_of_voice} tone of voice."
        ];

        if (!empty($data->include_hashtags) && $data->include_hashtags > 0) {
            $instructions[] = "Include exactly {$data->include_hashtags} relevant and trending hashtags.";
        }

        // Final prompt
        $prompt = implode(' ', $instructions);

        try {
            // Call AI service
            $result = AI::process($prompt, 'text', [
                'maxResult' => 1
            ], $teamId);

            $data = $result['data'] ?? [];

            if (!empty($data)) {
                // Clean output
                $caption = trim($data[0]);
                $caption = trim($caption, '"\' ');
                return $caption;
            }

            throw new \Exception("Empty AI response for caption generation.");
        } catch (\Throwable $e) {
            throw new \Exception("Caption generation failed: " . $e->getMessage());
        }
    }

    /**
     * Generate title based on caption.
     *
     * Constructs a prompt based on the given caption and the provided configuration options,
     * then calls generate_text() to produce a title. The function validates the response and cleans
     * up the output before returning the final title.
     *
     * @param string $caption The caption for which to generate a title.
     * @param object $data    An object containing:
     *                        - language (string): The desired language for the title.
     *                        - tone_of_voice (string): The desired tone for the title.
     *
     * @return string         The generated title.
     * @throws \Exception     If the response structure is invalid.
     */
    private function generateTitle(string $caption, object $data, int $teamId): string
    {
        // Build the prompt instructions
        $instructions = [
            "Create a short and catchy title based on the following caption:",
            "\"{$caption}\"",
            "Write in {$data->language}.",
            "Do not exceed 15 words.",
            "Use a {$data->tone_of_voice} tone of voice.",
            "Do not include any hashtags or special characters.",
            "Keep it suitable for a social media post title."
        ];

        // Join the instructions into a final prompt
        $prompt = implode(' ', $instructions);

        try {
            // Generate text using AI service
            $result = AI::process($prompt, 'text', [
                'maxResult' => 1
            ], $teamId);

            $data = $result['data'] ?? [];

            if (!empty($data)) {
                // Clean the output
                $title = $data[0] ?? '';
                $title = trim(preg_replace('/\s+/', ' ', $title));  // remove newlines, extra spaces
                $title = trim($title, "\"' #-.");                    // strip quotes, hashtags, dashes, dots
                return $title;
            }

            throw new \Exception("Empty AI response for title generation.");
        } catch (\Throwable $e) {
            throw new \Exception("Title generation failed: " . $e->getMessage());
        }
    }

    /**
     * Update advance options with generated title
     */
    private function updateOptions(&$options, $title)
    {
        foreach ($options as $key => &$value) {
            if (str_ends_with($key, "_title") && empty($value)) {
                $value = $title;
            }
        }
    }

    /**
     * Fetch images from selected source
     */
    private function fetchImages($source, $caption, $teamId)
    {
        $medias = [];
        if (!$source) return $medias;

        switch ($source) {
            case 'ai':
                $result = AI::process($caption, 'image', [
                    'size' => '1024x1024'
                ], $teamId);

                $medias = $result['data'] ?? [];
                break;
            case 'unsplash':
            case 'pexels_photo':
            case 'pexels_video':
            case 'pixabay_photo':
            case 'pixabay_video':

                $mediaResults = \SearchMedia::find($caption, $source);
                $medias = [];
                if (is_array($mediaResults) && !empty($mediaResults)) {
                    $mediaData = $mediaResults[array_rand($mediaResults)];
                    $medias[] = $mediaData['full'];
                }
                break;
            default:
                if (is_numeric($source)) {
                    $file = DB::table('files')->where(['pid' => $source, 'team_id' => $teamId])->first();
                    if ($file) {
                        $medias[] = Media::url($file->file);
                    }
                }
                break;
        }

        return $medias;
    }

    /**
     * Prepare data for social media posting
     *
     * Constructs the posting data for each account and returns an array
     * of objects. Each object contains the required fields needed for a post.
     *
     * @param array $accounts List of account objects.
     * @param mixed $postId   The query/post ID.
     * @param string $caption The caption to use in the post.
     * @param array $medias   An array of media items, if any.
     * @param mixed $options  Additional posting options.
     *
     * @return object[]       An array of objects containing the prepared post data.
     */
    private function preparePostData($accounts, $postId, $caption, $medias, $options)
    {
        if (is_array($accounts)) {
            if (!empty($accounts) && is_numeric(reset($accounts))) {
                $accounts = Accounts::whereIn('id', array_map('intval', $accounts))->get();
            } else {
                $accounts = collect($accounts);
            }
        }

        if ($accounts instanceof \Illuminate\Support\Collection === false) {
            $accounts = collect($accounts ?: []);
        }

        $options = is_array($options) ? $options : (array) $options;
        $type    = !empty($medias) ? 'media' : 'text';
        $now     = time();

        return $accounts->filter()->map(function ($account) use ($postId, $caption, $medias, $options, $type, $now) {
            if (!isset($account->id, $account->team_id, $account->social_network)) {
                return null;
            }

            return (object) [
                'id_secure'        => rand_string(),
                'team_id'          => $account->team_id,
                'campaign'         => 0,
                'labels'           => json_encode([]),
                'account_id'       => $account->id,
                'social_network'   => $account->social_network,
                'category'         => $account->category ?? '',
                'module'           => $account->module ?? '',
                'function'         => 'post',
                'api_type'         => $account->login_type ?? '',
                'type'             => $type,
                'method'           => 'ai',
                'query_id'         => $postId,
                'data'             => json_encode([
                    'caption' => trim((string) $caption),
                    'link'    => '',
                    'medias'  => $medias ?: [],
                    'options' => $options,
                ]),
                'time_post'        => $now,
                'delay'            => 0,
                'repost_frequency' => 0,
                'result'           => '',
                'status'           => 3,
                'changed'          => $now,
                'created'          => $now,
            ];
        })->filter()->values()->all();
    }

    /**
     * Validate and post data to social media
     */
    private function handlePosting($post, $postData)
    {
        $validator = Publishing::validate($postData);
        $canPost = json_decode($validator["can_post"]);

        if (!empty($canPost) || $validator["status"] === "success") {
            $result = Publishing::post($postData, $canPost);
            $this->logResult($post->id, 1, $result['message']);
        }
    }

    /**
     * Log errors and stop schedule
     */
    private function logAndStop($post, $message)
    {
        $this->logResult($post->id, 0, $message);
        AIPosts::where('id', $post->id)->update(["status" => 0]);
    }

    /**
     * Log the result
     */
    private function logResult($postId, $status, $message)
    {
        $logs = AIPosts::where('id', $postId)->value('result') ?: "[]";
        $logs = json_decode($logs, true);
        $logs[] = ["status" => $status, "message" => __($message), "time" => time()];
        $logs = array_slice($logs, -1); // Keep only last 1 logs

        AIPosts::where('id', $postId)->update([
            "result" => json_encode($logs)
        ]);
    }
}