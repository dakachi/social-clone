<?php

namespace Modules\AdminCrons\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Console\Scheduling\Schedule;

class AdminCronsController extends Controller
{
    public function index()
    {
        $cronInfos = app()->bound('crons') ? app('crons') : [];

        $cronInfoMap = [];
        foreach ($cronInfos as $cronInfo) {
            foreach ($cronInfo as $command => $url) {
                if (in_array($command, ['icon', 'color', 'id', 'key', 'module_name'])) continue;
                $cronInfoMap[$command] = [
                    'url'         => $url,
                    'icon'        => $cronInfo['icon'] ?? null,
                    'color'       => $cronInfo['color'] ?? null,
                    'id'          => $cronInfo['id'] ?? null,
                    'key'         => $cronInfo['key'] ?? null,
                    'module_name' => $cronInfo['module_name'] ?? null,
                ];
            }
        }

        $schedule = app(Schedule::class);
        app()->boot();
        $events = $schedule->events();

        $crons = [];
        foreach ($events as $event) {
            $cmdStr  = method_exists($event, 'getSummaryForDisplay') ? $event->getSummaryForDisplay() : $event->command;
            $cmdName = $this->getCommandNameFromString($cmdStr);
            $meta    = $cronInfoMap[$cmdName] ?? [];

            $crons[] = [
                'command_name' => $cmdName,
                'command'      => $cmdStr,
                'full_command' => $this->getFullCronCommand($cmdName),
                'expression'   => $event->expression,
                'timezone'     => $event->timezone,
                'next_due'     => $event->nextRunDate()->format('Y-m-d H:i:s'),
                'description'  => $event->description,
                'url'          => $meta['url'] ?? null,
                'icon'         => $meta['icon'] ?? null,
                'color'        => $meta['color'] ?? null,
                'id'           => $meta['id'] ?? null,
                'key'          => $meta['key'] ?? null,
                'module_name'  => $meta['module_name'] ?? null,
            ];
        }

        return view('admincrons::index', ['crons' => $crons]);
    }

    private function getFullCronCommand($cmdName)
    {
        $phpPath     = PHP_BINDIR . '/php';
        if (stripos(PHP_OS, 'WIN') === 0) {
            $phpPath = 'php';
        }
        $artisanPath = base_path('artisan');
        return "{$phpPath} {$artisanPath} {$cmdName} > /dev/null 2>&1";
    }

    private function getCommandNameFromString($cmdStr)
    {
        if (is_array($cmdStr)) {
            foreach ($cmdStr as $i => $part) {
                if ($part == 'artisan' && isset($cmdStr[$i + 1])) {
                    return ltrim($cmdStr[$i + 1], ':');
                }
            }
            $cmdStr = implode(' ', $cmdStr);
        }

        $cmdStr = trim($cmdStr, '"\' ');

        if (preg_match('/artisan\s+([\w\-:]+)/', $cmdStr, $m)) {
            return $m[1];
        }
        if (preg_match('/artisan"\s+([\w\-:]+)/', $cmdStr, $m)) {
            return $m[1];
        }
        if (preg_match('/([\w\-:]+)(\s|$)/', $cmdStr, $m)) {
            return $m[1];
        }

        return $cmdStr;
    }

    public function change()
    {
        $newKey = rand_string();

        update_option("cron_key", $newKey);

        ms([
            "status" => 1,
            "message" => __("Cron key updated successfully"),
            "redirect" => module_url()
        ]);
    }
}
