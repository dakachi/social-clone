<?php

namespace Modules\AdminAIConfiguration\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AdminAIConfigurationServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'AdminAIConfiguration';
    protected string $nameLower = 'adminaiconfiguration';

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
            "sort" => 1000,
            "view" => "permissions",
        ]);

        \Credit::addCreditRates($this->name, [
            "view" => "credit-rates"
        ]);

        // 👇 Nếu bảng chưa có thì tạo mới
        $this->createAiModelsTableIfNotExists();

        // 👇 Nếu bảng rỗng thì import model mặc định từ AIService
        $this->importAiModelsIfEmpty();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerCommands(): void {}
    protected function registerCommandSchedules(): void {}

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

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        $componentNamespace = $this->module_namespace($this->name, $this->app_path(config('modules.paths.generator.component-class.path')));
        Blade::componentNamespace($componentNamespace, $this->nameLower);
    }

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

    /**
     * Nếu bảng ai_models chưa tồn tại thì tạo mới
     */
    private function createAiModelsTableIfNotExists(): void
    {
        if (!Schema::hasTable('ai_models')) {
            Schema::create('ai_models', function (Blueprint $table) {
                $table->id();
                $table->string('id_secure', 50)->nullable()->unique();
                $table->string('provider');       // openai, claude, gemini, deepseek...
                $table->string('model_key');      // gpt-4o, gpt-5, claude-haiku...
                $table->string('name');           // Friendly name
                $table->string('category')->default('text'); 
                $table->string('type')->nullable(); 
                $table->boolean('is_active')->default(true);
                $table->string('api_type')->default('chat')
                      ->comment('API endpoint type: chat, responses, audio, image, video, embedding...');
                $table->json('api_params')->nullable()
                      ->comment('Custom API params mapping, e.g., {"max_tokens":"max_output_tokens"}');
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->unique(['provider', 'model_key', 'category']);
            });

            \Log::info('[AdminAIConfiguration] Created table ai_models automatically.');
        }
    }

    /**
     * Nếu bảng ai_models rỗng thì import models từ AIService
     */
    private function importAiModelsIfEmpty(): void
    {
        if (!Schema::hasTable('ai_models')) {
            return;
        }

        if (DB::table('ai_models')->count() > 0) {
            return; // bảng đã có data
        }

        try {
            $models = \AI::getLatestModels();
            \AI::syncModels($models);

            \Log::info('[AdminAIConfiguration] Auto-imported AI models because table was empty.');
        } catch (\Exception $e) {
            \Log::error('[AdminAIConfiguration] Auto import failed: ' . $e->getMessage());
        }
    }
}
