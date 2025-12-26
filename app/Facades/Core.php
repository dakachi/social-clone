<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\ModuleStatus;
use Illuminate\Support\Number;
use Modules\AdminLanguages\Models\Language;
use Carbon\Carbon;

class Core extends Facade
{
    protected static $sidebarBlocks = [];

    protected static function getFacadeAccessor()
    { 
        return 'core';
    }

    protected static function startPage( $path = "" )
    { 
        return url_app("dashboard");
    }

    protected static function sidebarColor()
    {
        $colorType = get_option('backend_sidebar_icon_color', 1);

        if($colorType == 0){
            return "style='color: ".get_option('backend_site_icon_color', '')."!important'";
        }else{
            return '';
        }
    }

    protected static function currency($number, $in = "USD")
    {
        return price($number);
    }

    public static function parseDateRange($request, $defaultDays = 30)
    {
        if ($request->has('daterange')) {
            [$start, $end] = explode(',', $request->daterange);
            $startDate = Carbon::parse(trim($start))->startOfDay();
            $endDate = Carbon::parse(trim($end))->endOfDay();
        } else {
            $startDate = $request->has('startDate')
                ? Carbon::parse($request->startDate)->startOfDay()
                : Carbon::now()->subDays($defaultDays)->startOfDay();

            $endDate = $request->has('endDate')
                ? Carbon::parse($request->endDate)->endOfDay()
                : Carbon::now()->endOfDay();
        }

        return [$startDate, $endDate];
    }

    protected static function addSubMenu($module_name, $sub_menu)
    {
        $sub_menus = app()->bound('sub_menus') ? app('sub_menus') : [];

        if (isset($sub_menus[$module_name])) {
            $sub_menus[$module_name] = array_merge($sub_menus[$module_name], $sub_menu);
        } else {
            $sub_menus[$module_name] = $sub_menu;
        }

        app()->instance('sub_menus', $sub_menus);
    }

    protected static function addSidebarBlock($item, int $priority = 100, callable $visible = null)
    {
        self::$sidebarBlocks[] = [
            'item'     => $item,
            'priority' => $priority,
            'visible'  => $visible,
        ];
    }

    protected static function getSidebarBlocks(): array
    {
        $items = self::$sidebarBlocks;

        // Sort items by priority (lower = earlier)
        usort($items, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $items;
    }

    protected static function loadModuleAssets()
    {
        Cache::forget('module_assets');
        return Cache::remember('module_assets', now()->addDay(), function () {
            $modulesDir = base_path('modules');
            $modules = scandir($modulesDir);

            $cssFiles = [];
            $jsFiles = [];

            foreach ($modules as $module) {
                if ($module !== '.' && $module !== '..') {
                    $moduleJsonPath = $modulesDir . '/' . $module . '/module.json';

                    if (file_exists($moduleJsonPath)) {
                        $moduleJson = json_decode(file_get_contents($moduleJsonPath), true);

                        if (isset($moduleJson['assets'])) {
                            if (isset($moduleJson['assets']['css'])) {
                                foreach ($moduleJson['assets']['css'] as $cssFile) {
                                    $cssFiles[] =  url('modules/' . $module . '/' . $cssFile);
                                }
                            }

                            if (isset($moduleJson['assets']['js'])) {
                                foreach ($moduleJson['assets']['js'] as $jsFile) {
                                    $jsFiles[] = url('modules/' . $module . '/' . $jsFile);
                                }
                            }
                        }
                    }
                }
            }

            return [
                'css' => $cssFiles,
                'js' => $jsFiles
            ];
        });

    }

    protected static function addModulesToDatabase()
    {
        $modulesDir = base_path('modules');
        $modules = scandir($modulesDir);
        $newModuleAdded = false;

        foreach ($modules as $module) {
            if ($module !== '.' && $module !== '..') {
                // Check if the module exists in the database
                $moduleExists = ModuleStatus::where('module', $module)->exists();
                if (!$moduleExists) {
                    // Add the module to the database with the default status as enabled
                    ModuleStatus::create([
                        'module' => $module,
                        'enabled' => true  // Default is enabled
                    ]);
                    $newModuleAdded = true;  // Set flag if a new module is added
                }
            }
        }

        // Update the cache to store the status of modules
        Cache::put('module_assets', self::loadModuleAssets(), now()->addDay());

        return $newModuleAdded;
    }


    protected static function updateModuleStatusesFile()
    {
        if (Schema::hasTable('module_statuses')) {
            self::addModulesToDatabase();
            
            // Retrieve all module statuses from the database
            $moduleStatuses = ModuleStatus::all()->pluck('enabled', 'module')->map(function ($enabled) {
                return (bool) $enabled; // Convert the 'enabled' value to boolean
            })->toArray();

            // Path to the modules_statuses.json file
            $filePath = base_path('resources/modules_statuses.json');

            // Check if the modules_statuses.json file exists
            if (!File::exists($filePath)) {
                // If the file does not exist, create a new modules_statuses.json file
                File::put($filePath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));
                header('Location: ' . url()->current());
                exit;
            } else {
                File::put($filePath, json_encode($moduleStatuses, JSON_PRETTY_PRINT));
            }

            return true;
        }
    }

    /**
     * Update the module.json file with new color values for menu->color
     * and (if present) menu->sub_menu->color.
     *
     * @param string $filePath The path to the module.json file.
     * @param string $menuColor The new color for the menu (default: "#333333").
     * @param string $subMenuColor The new color for the sub_menu (default: "#444444").
     * @return string A success message if updated successfully.
     * @throws Exception if the file does not exist or if there's an error.
     */
    protected static function updateModuleJsonColors($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("The file {$filePath} does not exist.");
        }

        // Read the module.json file and convert its contents into a PHP array.
        $jsonContent = file_get_contents($filePath);
        $moduleData = json_decode($jsonContent, true);

        if (!$moduleData || !isset($moduleData['menu'])) {
            throw new \Exception("The file {$filePath} is invalid or missing the 'menu' field.");
        }

        if (stripos($moduleData['menu']['id'], "Channels") === false) {
            // Update the 'menu' color field.
            $moduleData['menu']['color'] = generateSinglePastelColor();

            // If a sub_menu exists, iterate through each entry and update its color (if it exists).
            if (isset($moduleData['menu']['sub_menu']) && is_array($moduleData['menu']['sub_menu'])) {
                foreach ($moduleData['menu']['sub_menu'] as &$subMenu) {
                    if (isset($subMenu['color'])) {
                        $subMenu['color'] = generateSinglePastelColor();
                    }
                }
            }

            // Re-encode the array to JSON with pretty print and unescaped slashes.
            $newJsonContent = json_encode($moduleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if (file_put_contents($filePath, $newJsonContent) === false) {
                throw new \Exception("Failed to write to {$filePath}");
            }

            return "module.json successfully updated: {$filePath}";
        }

    }

    /**
     * Scan the modules directory and update all module.json files.
     *
     * @param string $modulesFolder The path to the modules folder (default: "modules").
     * @param string $menuColor The new color for menu->color.
     * @param string $subMenuColor The new color for menu->sub_menu->color.
     * @return array An array of update messages or errors for each file.
     */
    protected static function updateAllModuleJsonColors()
    {
        $results = [];
        $modulesFolder = base_path('modules');

        // Use glob to get all module.json files from subdirectories of the modules folder.
        $files = glob($modulesFolder . '/*/module.json');
        
        foreach ($files as $file) {
            try {
                $results[] = self::updateModuleJsonColors($file);
            } catch (\Exception $e) {
                $results[] = "Error in {$file}: " . $e->getMessage();
            }
        }
        return $results;
    }
}

