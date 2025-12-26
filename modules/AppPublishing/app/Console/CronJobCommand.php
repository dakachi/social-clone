<?php

namespace Modules\AppPublishing\Console;

use Illuminate\Console\Command;
use Modules\AppPublishing\Models\Posts;
use Modules\AppPublishing\Facades\Publishing;
use Carbon\Carbon;
use DB;

class CronJobCommand extends Command
{
    protected $signature = 'apppublishing:cron';
    protected $description = 'Cronjob: Publish processing posts to social networks';

    public function handle()
    {
        $now = Carbon::now()->timestamp;

        $posts = Posts::where('status', 3)
            ->where('time_post', '<=', $now)
            ->limit(10)
            ->get();


        if ($posts->isEmpty()) {
            return 0;
        }

        foreach ($posts as $post) {
            Publishing::post([$post]);
        }

        return 0;
    }
}
