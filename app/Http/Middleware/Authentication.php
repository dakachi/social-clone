<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Gate;
use Modules\AdminUsers\Models\Teams;
use Modules\AdminUsers\Models\TeamMembers;
use App\Models\User;

class Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (config('app.demo') || env('DEMO_MODE', false)) {
            $except = [
                'app/*/list',
                'app/dashboard/statistics',
                'app/publishing/composer',
                'app/publishing/events',
                'app/publishing/preview',
                'app/ai-contents/popup-ai-content',
                'app/captions/get_cation',
                'app/captions/list/popup',
                'app/files/upload_from_url',
                'app/files/update_folder',
                'app/files/mini_list',
                'app/ai-contents/categories',
                'app/ai-contents/templates',
                'app/groups/update',
                'app/watermark/load',
                'app/support/load-comment',
                'admin/statistics',
                'admin/demo',
                'admin/*/list',
                'admin/*/list/*',
                'admin/*/support/*',
                'admin/*/report/*',
                'admin/languages/translations-list/*',
                'auth/do_login',


            ];

            if ($request->is('admin*') || $request->is('app*') || $request->is('auth*')) {
                if (!$request->is($except)) {
                    if ($request->ajax()) {
                        return response()->json([
                            'class' => 'text-error text-danger',
                            'status' => 0,
                            'error_type' => 4,
                            'message' => 'This feature is disabled in demo mode.',
                        ]);
                    }
                    //abort(403, 'This feature is disabled in demo mode.');
                }
            }
        }

        if(Auth::check()){

            $user_id = Auth::id();
            $user = Auth::user();

            if($user->status == 0 && $user->role == 1){
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route("home");
            }

            /*
            * SET TIMEZONE
             */
            $timezone = $user->timezone ?? config('app.timezone');
            if (!$this->isValidTimezone($timezone)) {
                $timezone = 'UTC';
            }
            
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);

            /*
            * PROCESS TEAM
             */
            $current_team_id = session('current_team_id');

            if ($current_team_id) {
                $team = Teams::where('id', $current_team_id)->first();
                if (
                    !$team ||
                    ($team->owner != $user_id && !TeamMembers::where('team_id', $team->id)->where('uid', $user_id)->where('status', 1)->exists())
                ) {
                    $team = null;
                }
            }

            if (empty($team)) {
                $team = Teams::where("owner", $user_id)->first();
                if (empty($team) && $current_team_id) {
                    $team_member = TeamMembers::where('uid', $user_id)->where('status', 1)->first();
                    if ($team_member) {
                        $team = Teams::where('id', $team_member->team_id)->first();
                    }
                }

                if ($team) {
                    session([
                        'current_team_id' => $team->id,
                        'current_team_secure' => $team->id_secure,
                        'current_team_name' => $team->name,
                    ]);
                }
            }

            if (empty($team)) {
                $plan = $user->plan ?? null;
                if (!$plan) {
                    Auth::guard('web')->logout();
                    Session::invalidate();
                    Session::regenerateToken();
                    return redirect()->route("home");
                }

                $planPermissions = is_string($plan->permissions)
                    ? json_decode($plan->permissions, true)
                    : ($plan->permissions ?? []);

                $team = new Teams();
                $team->owner = $user->id;
                $team->id_secure = rand_string();
                $team->permissions = $planPermissions;
                $team->save();
            }

            /*
            * END PROCESS TEAM
             */
            
            self::checkPermissions($user, $team);

            $request->team_id = $team->id;
            $request->team = $team;
            \Plan::doResetQuota($team->id);

            request()->merge(['team' => $team ]);
            request()->merge(['team_id' => $team->id]);
            view()->share("user", $user);
            view()->share("team", $team);

            $exceptModules = [
                "AppProfile",
                "Payment",
                "AppFiles",
            ];

            if(!session()->has("login_as")){
                session(["login_as" => "client"]);
            }

            // Handle session roles
            if(session('login_as') == "admin" && $request->segment(1) == "app" && !in_array( module("module_name"), $exceptModules)){
                return redirect()->route("admin.dashboard.index");
            }

            if( (session()->missing('login_as') && $request->segment(1) == "admin") || (session('login_as') == "client" && $request->segment(1) == "admin") ){
                return redirect()->route("app.dashboard.index");
            }

            if ($user->role == 1 && $request->segment(1) == "admin") {
                return redirect()->route("app.dashboard.index");
            }

            if (session()->missing('user_token')) {
                session(["user_token" => rand_string()]);
            }

            $this->registerSidebar(); 
            $this->bootModules();
        }

        \Script::define('VARIABLES', [
            'csrf' => csrf_token(),
            'url' => rtrim(url('/'), '/') . '/',
            'theme_asset' => theme_public_asset(''),
            'lang' => strtolower(app()->getLocale()),
            'format_date' => dateFormatJs(),
            'format_datetime' => dateTimeFormatJs(),
        ]);
        return $next($request);
    }

    protected function bootModules()
    {
        $modulesPath = base_path('modules');
        $modules = glob($modulesPath . '/*');

        foreach ($modules as $modulePath) {
            $bootFile = $modulePath . '/app/Hooks/boot.php';

            if (file_exists($bootFile)) {
                include_once $bootFile;
            }
        }
    }

    protected function checkPermissions($user, $team)
    {
        if ($team->owner == $user->id) {
            $rawPermissions = $team->permissions;
        } else {
            $member = \Modules\AdminUsers\Models\TeamMembers::where('team_id', $team->id)
                ->where('uid', $user->id)
                ->where('status', 1)
                ->first();
            $rawPermissions = $member && $member->permissions ? $member->permissions : [];
        }

        if (is_string($rawPermissions)) {
            $permissions = json_decode($rawPermissions, true);
        } else {
            $permissions = $rawPermissions;
        }
        if (is_array($permissions) && isset($permissions[0]['key']) && isset($permissions[0]['value'])) {
            $permissions = collect($permissions)->pluck('value', 'key')->toArray();
        }
        $permissions = is_array($permissions) ? $permissions : [];

        foreach ($permissions as $key => $val) {
            Gate::define($key, fn () => in_array($val, ['1', 1, true], true));
        }

        app()->instance('permissions', $permissions);

        $module = module();
        if ($module && $module['role'] == 'client') {
            $permissionKey = $module['key'];
            $needPermission = self::resolveModulePermission($module);

            if ($needPermission) {
                \Access::check($permissionKey, true, true);
            }
        }
    }

    protected function resolveModulePermission($module)
    {
        $permission = $module['permission'];
        $moduleKey = $module['key'];

        if (!$permission || $permission === false) {
            return false;
        }

        if ($permission == true) {
            return [$moduleKey];
        }

        if (is_array($permission)) {
            return array_column($permission, 'key');
        }

        return false;
    }

    protected function isValidMenuItem($menu_item): bool
    {
        return isset(
            $menu_item['uri'],
            $menu_item['section'],
            $menu_item['tab_id'],
            $menu_item['position'],
            $menu_item['name'],
            $menu_item['color'],
            $menu_item['icon'],
            $menu_item['role']
        );
    }

    protected function getModuleMenuConfig($module): ?array
    {
        $modulePath = $module->getPath() . '/module.json';

        if (file_exists($modulePath)) {
            $json = json_decode(file_get_contents($modulePath), true);
            return $json['menu'] ?? null;
        }

        return null;
    }

    public function registerSidebar(): void
    {
        $modules = \Module::all();
        $sidebar_top = [];
        $sidebar_bottom = [];
        $loginAs = session()->has("login_as") ? session("login_as") : "client";

        $sub_menus = [];
        $grouped_menus = [];

        foreach ($modules as $module) {
            if (!$module->isEnabled()) continue;

            $menu_item = $module->get("menu") ?? $this->getModuleMenuConfig($module);
            if (!$this->isValidMenuItem($menu_item)) continue;
            if ($menu_item['role'] != $loginAs) continue;
            $permissionKey = $module->getLowerName();

            if($loginAs == "client"){
                $permissionFlag = $module->get("permission");

                if ($permissionFlag === true) {
                    if (!\Access::canAccess($permissionKey, false)) continue;
                } elseif ($permissionFlag === -1) {
                    if (!Gate::has($permissionKey) || !\Access::canAccess($permissionKey, false)) continue;
                }
            }

            $menu_item['id'] = $permissionKey;
            $moduleName = $module->getName();
            $addSubMenus = app()->bound('sub_menus') ? app('sub_menus') : [];

            if (isset($addSubMenus[$moduleName])) {
                $menu_item['sub_menu'] ??= [
                    [
                        "uri" => $menu_item['uri'],
                        "name" => $menu_item['name'],
                        "position" => 999999999,
                        "icon" => $menu_item['icon'],
                        "color" => $menu_item['color'],
                    ]
                ];

                foreach ($addSubMenus[$moduleName] as $sub_menu) {
                    if (is_array($sub_menu)) {
                        $menu_item['sub_menu'][] = $sub_menu;
                    }
                }
            }

            if (!empty($menu_item['sub_menu']) && is_array($menu_item['sub_menu'])) {
                usort($menu_item['sub_menu'], fn($a, $b) => $a['position'] <=> $b['position']);
                foreach ($menu_item['sub_menu'] as $sub_menu_item) {
                    $sub_menus[$menu_item['name']][] = $sub_menu_item;
                }
            }

            if (!isset($grouped_menus[$menu_item['name']])) {
                $grouped_menus[$menu_item['name']] = $menu_item;
            }
        }

        foreach ($grouped_menus as &$menu_item) {
            if (isset($sub_menus[$menu_item['name']])) {

                $sub_menu_list = $sub_menus[$menu_item['name']];

                usort($sub_menu_list, function ($a, $b) {
                    return ($b['position'] ?? 0) <=> ($a['position'] ?? 0);
                });

                $menu_item['sub_menu'] = $sub_menu_list;
            }

            if ($menu_item['section'] === "top") {
                $sidebar_top[] = $menu_item;
            } elseif ($menu_item['section'] === "bottom") {
                $sidebar_bottom[] = $menu_item;
            }
        }

        uasort($sidebar_top, 'cmp_sidebar');
        uasort($sidebar_bottom, 'cmp_sidebar');

        view()->share('sidebar', [
            'top' => array_values($sidebar_top),
            'bottom' => array_values($sidebar_bottom),
        ]);
    }

    protected function isValidTimezone($tz)
    {
        return in_array($tz, \DateTimeZone::listIdentifiers());
    }
}
