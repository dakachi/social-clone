<?php

namespace Modules\AdminCrons\Facades;

use Illuminate\Support\Facades\Facade;

class CronService extends Facade
{
    protected static function getFacadeAccessor()
    { 
        return 'cronservice';
    }

    protected static function addCron($module_name, $cron) 
    {
        $module = \Module::find($module_name);
        $menu = $module->get('menu');
        if ($menu) {
            $cron = array_merge($cron, [
                'icon' => $menu['icon'],
                'color' => $menu['color'],
                'id' => $module->getName(),
                'key' => $module->getLowerName(),
                'module_name' => $menu['name'],
            ]);

            // Get current instance crons or fallback to empty array
            $crons = app()->bound('crons') ? app('crons') : [];

            $crons[] = $cron;

            // Bind updated crons array back into the container
            app()->instance('crons', $crons);
        }
    }

    public static function notify($message, $type = 'info', $command = null)
    {
        if ($command && app()->runningInConsole()) {
            if ($type === 'error') {
                $command->error($message);
            } else {
                $command->info($message);
            }
        } else {
            if ($type === 'error') {
                \Log::error($message);
            } else {
                \Log::info($message);
            }
            return $message;
        }
    }

}


