<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Blade;
use Hexadog\ThemesManager\Facades\ThemesManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Modules\AdminUsers\Models\Teams;
use App\Translation\CustomFileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Core;
use Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
        $cachePath = storage_path('framework/cache');
        if (!File::exists($cachePath)) {
            File::makeDirectory($cachePath, 0777, true);
        }

        $cachePath = storage_path('framework/views');
        if (!File::exists($cachePath)) {
            File::makeDirectory($cachePath, 0777, true);
        }

        $this->app->singleton('translation.loader', function ($app) {
            $path = $app['path.lang'];
            return new CustomFileLoader(new Filesystem, $path);
        });

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];
            $locale = $app['config']['app.locale'];
            $translator = new Translator($loader, $locale);

            $translator->setFallback($app['config']['app.fallback_locale']);

            return $translator;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            \URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);
        $this->registerAuth();
        $this->registerModule();
        $this->registerHelper();
        $this->registerDB();
        $this->registerBlade();
        $this->registerFacades();
        Core::updateModuleStatusesFile();
    }

    public function registerModule(): void
    {
        $modules = Module::all();

        foreach ($modules as $key => $module)
        {
            if($menu_item = $module->get("menu"))
            {
                if(isset($menu_item['uri']))
                {
                    $contains = Str::contains(url()->current(), [ $menu_item['uri'] ]);
                    if($contains)
                    {
                        if(request()->module == "" || strlen(request()->module['uri']) < strlen($menu_item['uri']) ){
                            $menu_item['permission'] = $module->get('permission');
                            $menu_item['module_name'] = $module->getName();
                            $menu_item['key'] = $module->getLowerName();
                            $menu_item['module_path'] = $module->getPath();
                            $menu_item['module_desc'] = $module->getDescription();
                            request()->merge(['module' => $menu_item]);
                        }
                    }
                }

            }
        }
    }

    public function registerAuth(){
        view()->composer('*', function($view){
            if(Auth::check()){
                $user_id = Auth::id();
                $user = Auth::user();
                $team = Teams::where("owner", $user_id)->first();

                if (!$team) {
                    Auth::guard('web')->logout();
                    Session::invalidate();
                    Session::regenerateToken();
                    header("Location: ". url(""));
                }

                request()->merge(['team' => $team ]);
                request()->merge(['team_id' => $team->id]);

                $view->with("user", $user);
                $view->with("team", $team);
            }
        });
    }

    public function registerFacades(): void{
        $folderPath =  base_path('/app/Facades');

        $files = glob($folderPath . '/*.php');

        foreach ($files as $file) {
            // Extract the class name from the filename
            $className = basename($file, '.php');

            // Check if the class exists and create alias
            if (!class_exists($className)) {
                class_alias("\\App\\Facades\\{$className}", $className);
            }
        }
    }

    public function registerDB(): void
    {
        $options = DB::table('options')->get();
        config(['options' => $options]);

        if( !Schema::hasTable('cache') ){
            Schema::create('cache', function ($table) {
                $table->string('key')->unique();
                $table->text('value');
                $table->integer('expiration');
            });
        }

        if( !Schema::hasTable('jobs') ){
            Schema::create('jobs', function ($table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at')->nullable();
                $table->unsignedInteger('created_at');
            });
        }

        if( !Schema::hasTable('sessions') ){
            Schema::create('sessions', function ($table) {
                $table->string('id')->unique();
                $table->integer('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('payload');
                $table->integer('last_activity');
            });
        }

        if( !Schema::hasTable('options') ){
            Schema::create('options', function ($table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->longText('value')->nullable();
            });
        }

        if( !Schema::hasTable('users') ){
            Schema::create('users', function ($table) {
                $table->increments('id');
                $table->string('id_secure')->nullable();
                $table->integer('role')->nullable();
                $table->string('pid')->nullable();
                $table->string('login_type')->nullable();
                $table->string('fullname')->nullable();
                $table->string('username')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('avatar')->nullable();
                $table->integer('plan')->nullable();
                $table->integer('expiration_date')->nullable();
                $table->string('timezone')->nullable();
                $table->string('language')->nullable();
                $table->mediumText('data')->nullable();
                $table->string('secret_key')->nullable();
                $table->integer('status')->nullable();
                $table->integer('changed')->nullable();
                $table->integer('created')->nullable();
            });

            DB::table('users')->insert([
                'id_secure' => '',
                'pid' => '',
                'login_type' => 'direct',
                'fullname' => 'Super Admin',
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => '123456',
                'plan' => '1',
                'expiration_date' => '-1',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'language' => 'en',
                'data' => '',
                'secret_key' => '123456789',
                'status' => 1,
                'changed' => 1687230216,
                'created' => 1687230216,
            ]);
        }

        if( !Schema::hasTable('languages') ){
            Schema::create('languages', function ($table) {
                $table->increments('id');
                $table->string('id_secure')->nullable();
                $table->string('name')->nullable();
                $table->string('code', 10)->nullable();
                $table->string('icon', 32)->nullable();
                $table->string('dir', 3)->nullable();
                $table->integer('is_default')->nullable();
                $table->string('auto_translate', 12)->nullable();
                $table->integer('status')->nullable();
                $table->integer('changed')->nullable();
                $table->integer('created')->nullable();
            });
        }
    }

    public function registerHelper(): void
    {
        $modules = Module::all();
        foreach ($modules as $key => $module) {
            $helper_dir = $module->getExtraPath('helpers');
            if( is_dir( $helper_dir ) )
            {
                $helper_files = glob($helper_dir."/*.php");
                if( !empty($helper_files) ){
                    foreach ($helper_files as $helper_file) {
                        include_once $helper_file;
                    }
                }
            }
        }
    }

    public function registerBlade(): void
    {
        Blade::extend(function($value) {
            return preg_replace('/\{\?(.+)\?\}/', '<?php ${1} ?>', $value);
        });

        Blade::if('cans', function (...$abilities) {
            $user = auth()->user();
            foreach ($abilities as $ability) {
                if (!$user || !$user->can($ability)) return false;
            }
            return true;
        });

        Blade::if('canany', function (...$abilities) {
            $user = auth()->user();
            foreach ($abilities as $ability) {
                if ($user && $user->can($ability)) {
                    return true;
                }
            }
            return false;
        });

    }
}


