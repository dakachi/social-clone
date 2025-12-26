<?php

namespace Modules\AppPublishing\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Modules\AppPublishing\Console\CronJobCommand;

class AppPublishingServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'AppPublishing';

    protected string $nameLower = 'apppublishing';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        \Plan::addPermissions($this->name, [
            "sort" => 3000,
            "view" => "permissions",
        ]);

        if (!class_exists('PublishingReport')) {
            class_alias(\Modules\AppPublishing\Facades\PublishingReport::class, 'PublishingReport');
        }

        if (!class_exists('Publishing')) {
            class_alias(\Modules\AppPublishing\Facades\Publishing::class, 'Publishing');
        }

        \AdminDashboard::registerDashboardItem(function () {
            return view('apppublishing::partials.admin-dashboard-item')->render();
        }, 11000, fn() => 1);
        
        \AppDashboard::registerDashboardItem(function () {
            return view('apppublishing::partials.dashboard-item')->render();
        }, 15000, fn() => 1);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Register module commands
        $this->commands([
            CronJobCommand::class,
        ]);

        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        \Pricing::addSubFeatures([
            "sort"      => 10,
            "parent"    => "features",
            "tab_id"    => 'publishing',
            "tab_name"  => __("Publishing"),
            "key"       => "apppublishing",
            "label"     => __("Calendar Publishing"),
            "check"     => true,
            "type"      => "boolean",
            "raw"       => 0,
        ]);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $schedule->command('apppublishing:cron')->everyMinute();
        });

        \CronService::addCron($this->name, [
            'apppublishing:cron' => url_app('publishing/cron?key=' . get_option('cron_key', rand_string())),
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $relativeConfigPath = config('modules.paths.generator.config.path');
        $configPath = module_path($this->name, $relativeConfigPath);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $configKey = $this->nameLower . '.' . str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $relativePath);
                    $key = ($relativePath === 'config.php') ? $this->nameLower : $configKey;

                    $this->publishes([$file->getPathname() => config_path($relativePath)], 'config');
                    $this->mergeConfigFrom($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        $componentNamespace = $this->module_namespace($this->name, $this->app_path(config('modules.paths.generator.component-class.path')));
        Blade::componentNamespace($componentNamespace, $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
