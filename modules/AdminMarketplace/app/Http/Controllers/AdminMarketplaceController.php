<?php

namespace Modules\AdminMarketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AdminAddons\Models\Addon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminMarketplaceController extends Controller
{
    protected const API_BASE_URL = 'https://stackposts.com/api/marketplace/';

    public function addons(Request $request)
    {
        $addons = Addon::orderByDesc('is_main')
        ->orderByDesc('id')
        ->paginate(20);

        $remoteProducts = [];
        $response = Http::withoutVerifying()->get(self::API_BASE_URL . 'all-products');

        if ($response->ok() && isset($response['data'])) {
            foreach ($response['data'] as $item) {
                $remoteProducts[$item['product_id']] = [
                    'version'     => $item['version'],
                    'name'        => $item['name'] ?? '',
                    'description' => $item['description'] ?? '',
                    'thumbnail'   => $item['thumbnail'] ?? null,
                ];
            }
        }

        $addons->getCollection()->transform(function($addon) use ($remoteProducts) {
            $module = \Module::find($addon->module_name??'');

            if ($module) {
                $menu = $module->get('menu');
                $addon->version         = $addon->version != "" ? $addon->version : $menu['version'] ?? null;
                $addon->uri         = $menu['uri'] ?? null;
                $addon->name        = $menu['name'] ?? \Str::headline($addon->module_name);
                $addon->color       = $menu['color'] ?? '#000';
                $addon->icon        = $menu['icon'] ?? 'fa-light fa-puzzle';
                $addon->description = $menu['description'] ?? __('Powerful, plug-and-play modules to extend your website with new features, tools, and integrations—easy to install and manage for everyone.');
            } else {
                $addon->uri         = "";
                $addon->name        = \Str::headline($addon->module_name);
                $addon->color       = '#000';
                $addon->icon        = 'fa-light fa-puzzle';
                $addon->description = __('Powerful, plug-and-play modules to extend your website with new features, tools, and integrations—easy to install and manage for everyone.');
            }

            $addon->has_update = false;
            if (isset($remoteProducts[$addon->product_id])) {
                $remoteVersion = $remoteProducts[$addon->product_id]['version'];
                if (version_compare($remoteVersion, $addon->version, '>')) {
                    $addon->has_update = true;
                    $addon->latest_version = $remoteVersion;
                }

                $addon->name        = $remoteProducts[$addon->product_id]['name'] ?? $addon->name;
                $addon->description = $remoteProducts[$addon->product_id]['description'] ?? $addon->description;
                $addon->thumbnail   = $remoteProducts[$addon->product_id]['thumbnail'] ?? null;
            }

            return $addon;
        });

        return view(module('key') . '::addons', compact('addons'));
    }

    public function index(Request $request)
    {
        $page    = $request->get('page', 1);
        $perPage = $request->get('per_page', 30);

        // Get list of modules from marketplace API
        $response = Http::withoutVerifying()->get(self::API_BASE_URL . 'products', [
            'page'     => $page,
            'per_page' => $perPage,
            'search'   => $request->get('search'),
        ]);

        if (!$response->ok() || !isset($response['data'], $response['meta'])) {
            return back()->with('error', __('Unable to load Marketplace data.'));
        }

        // Get locally installed addons
        $installedAddons = Addon::where('source', 1)->get()->keyBy('product_id');

        // Map status for each module
        $data = collect($response['data'])->map(function ($item) use ($installedAddons) {
            $addon = $installedAddons[$item['id']] ?? null;

            $item['installed'] = $addon ? true : false;
            $item['installed_version'] = $addon->version ?? null;
            $item['addon_status'] = $addon->status ?? null; // 1 = active, 0 = deactive

            if ($addon && $addon->status == 1) {
                $item['has_update'] = version_compare($item['version'], $addon->version, '>');
            } else {
                $item['has_update'] = false;
            }

            return $item;
        });

        $meta = $response['meta'];

        $keepQueryKeys = ['search', 'page', 'per_page', 'filter']; 
        $query = collect($request->query())->only($keepQueryKeys)->toArray();
        $modules = new LengthAwarePaginator(
            $data,
            $meta['total'],
            $meta['per_page'],
            $meta['current_page'],
            [
                'path' => $request->url(), 
                'query' => $query
            ]
        );

        return view(module('key') . '::index', compact('modules'));
    }

    public function detail($slug)
    {
        $response = Http::withoutVerifying()->get(self::API_BASE_URL . 'product-detail/' . $slug);

        if (!$response->ok() || empty($response['product'])) {
            return redirect()->route('admin.marketplace.index')->with('error', 'Module not found.');
        }

        $product   = $response['product'];
        $faqs      = $response['faqs'] ?? [];
        $support   = $response['support'] ?? [];
        $changelog = $response['changelog'] ?? [];

        $addon = Addon::where('product_id', $product['id'])->where('source', 1)->first();

        $installed         = $addon ? true : false;
        $installedStatus   = $addon->status ?? null;   // 1 = active, 0 = deactive
        $installedVersion  = $addon->version ?? null;
        $hasUpdate         = $addon && $addon->status == 1
            ? version_compare($product['version'], $addon->version, '>')
            : false;

        return view(module('key') . '::show', compact(
            'product', 'faqs', 'support', 'changelog',
            'installed', 'installedStatus', 'installedVersion', 'hasUpdate'
        ));
    }

    public function install(Request $request){
        ms([
            "status" => 1,
            "data" => view(module('key') . '::install')->render()
        ]);
    }

    public function doInstall(Request $request)
    {
        $request->validate([
            'purchase_code' => 'required|string',
        ]);

        $domain = preg_replace('/^www\./', '', $request->getHost());
        $purchaseCode = trim(preg_replace('/\s+/', '', $request->purchase_code));
        $verifyUrl = self::API_BASE_URL . 'install';

        // 1. Call API to verify purchase code and get addon/plugin info
        $response = Http::withoutVerifying()->post($verifyUrl, [
            'purchase_code' => $purchaseCode,
            'domain'        => $domain,
            'website'       => route('home'),
        ]);

        if (!$response->ok() || ($response['status'] ?? 0) != 1) {
            return response()->json([
                'status'  => 0,
                'message' => __($response['message'] ?? 'Purchase verification failed.')
            ]);
        }

        // 2. Get install info from API response
        $productId     = $response['product_id']    ?? null;
        $purchaseCode  = $request->purchase_code;
        $isMain        = $response['is_main']       ?? 0;
        $version       = $response['version']       ?? null;
        $installPath   = base_path($response['install_path']);
        $relativePath  = $response['install_path'];
        $moduleName    = $response['module_name'];

        if (!$productId || !$installPath || !$relativePath) {
            return response()->json([
                'status'  => 0,
                'message' => __('Missing install info from API response.')
            ]);
        }

        // 3. Download addon/plugin zip
        $downloadUrl = $response['download_url'] ?? null;
        if (!$downloadUrl) {
            return response()->json([
                'status'  => 0,
                'message' => __('Download URL not found.')
            ]);
        }
        $tempPath = storage_path('app/addons/tmp_' . \Str::random(8) . '.zip');
        try {
            \File::ensureDirectoryExists(dirname($tempPath));
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->get($downloadUrl);

            // Check HTTP code (200 OK)
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Download failed with status code: ' . $response->getStatusCode());
            }

            file_put_contents($tempPath, $response->getBody()->getContents());
        } catch (\Exception $e) {
            \Log::error('[Addon Install] Download failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 0,
                'message' => __('Download failed: :error', ['error' => $e->getMessage()])
            ]);
        }

        // 4. Extract to install directory
        try {
            $zip = new \ZipArchive();
            if ($zip->open($tempPath) === TRUE) {
                $zip->extractTo($installPath);
                $zip->close();
            } else {
                return response()->json([
                    'status'  => 0,
                    'message' => __('Unable to unzip addon.')
                ]);
            }
        } catch (\Exception $e) {
            \File::delete($tempPath);
            \Log::error('[Addon Install] Unzip failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 0,
                'message' => __('Failed to extract addon: :error', ['error' => $e->getMessage()])
            ]);
        }
        \File::delete($tempPath);

        // 5. Save install info into purchases (updateOrInsert by product_id + purchase_code)
        try {
            $addon = Addon::updateOrCreate(
                [
                    'product_id'    => $productId,
                    'purchase_code' => $purchaseCode,
                ],
                [
                    'id_secure'     => rand_string(),
                    'source'        => 1,
                    'module_name'   => $moduleName,
                    'is_main'       => $isMain,
                    'version'       => $version,
                    'install_path'  => $installPath,
                    'relative_path' => $relativePath,
                    'status'        => 1,
                    'changed'       => time(),
                    'created'       => time(),
                ]
            );
        } catch (\Exception $e) {
            \Log::error('[Addon Install] Save install record failed: ' . $e->getMessage());
            // Only log the error, do not block install
        }

        // 6. Return JSON result
        return response()->json([
            'status'        => 1,
            'message'       => __('Addon installed successfully.'),
            'id'            => $addon->id_secure,
            'product_id'    => $productId,
            'purchase_code' => $purchaseCode,
            'version'       => $version,
            'install_path'  => $installPath,
            'relative_path' => $relativePath,
            'is_main'       => $isMain,
            'time'          => now()->toDateTimeString(),
        ]);
    }

    public function doInstallZip(Request $request)
    {
        $request->validate([
            'module_zip' => 'required|file|mimes:zip',
        ]);

        // 1. Save the uploaded zip file to a temporary directory
        $zipFile = $request->file('module_zip');
        $tempPath = storage_path('app/tmp/' . uniqid() . '.zip');
        \File::ensureDirectoryExists(dirname($tempPath));
        $zipFile->move(dirname($tempPath), basename($tempPath));

        $zip = new \ZipArchive();
        if ($zip->open($tempPath) === true) {
            // 2. Get the root directory name in the zip (module folder)
            $firstEntry = $zip->getNameIndex(0);
            $moduleRoot = trim(explode('/', $firstEntry)[0]);

            // 3. Check for the existence of [moduleRoot]/module.json inside the zip
            $moduleJsonExists = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if ($entryName === $moduleRoot . '/module.json') {
                    $moduleJsonExists = true;
                    break;
                }
            }
            if (!$moduleJsonExists) {
                $zip->close();
                \File::delete($tempPath);
                return response()->json([
                    'status'  => 0,
                    'message' => __('The uploaded ZIP does not contain a valid module (missing module.json).')
                ]);
            }

            // 4. (Optional) Read meta info from module.json inside the zip
            $moduleMeta = [];
            $moduleJsonIndex = $zip->locateName($moduleRoot . '/module.json');
            if ($moduleJsonIndex !== false) {
                $moduleJsonContent = $zip->getFromIndex($moduleJsonIndex);
                $moduleMeta = json_decode($moduleJsonContent, true);
            }

            // 5. If the module already exists, remove it before installing new
            $extractPath = base_path('modules/' . $moduleRoot);
            if (\File::exists($extractPath)) {
                \File::deleteDirectory($extractPath);
            }

            // 6. Extract the module to the modules/ directory
            $zip->extractTo(base_path('modules'));
            $zip->close();
        } else {
            \File::delete($tempPath);
            return response()->json([
                'status'  => 0,
                'message' => __('Unable to open the ZIP file.'),
            ]);
        }
        \File::delete($tempPath);

        // 7. Save module info to the addons table
        try {
            $addon = Addon::updateOrCreate(
                ['module_name' => $moduleRoot],
                [
                    'id_secure'     => rand_string(),
                    'source'        => 2,
                    'status'        => 1,
                    'is_main'       => 0,
                    'version'       => $moduleMeta['version'] ?? '',
                    'install_path'  => base_path('modules/' . $moduleRoot),
                    'relative_path' => 'modules/' . $moduleRoot,
                    'created'       => now()->timestamp,
                    'changed'       => now()->timestamp,
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => __('Module installed, but failed to save info to the database: :error', ['error' => $e->getMessage()])
            ]);
        }

        return response()->json([
            'status'   => 1,
            'message'  => __('Module ":module" has been installed successfully!', ['module' => $moduleRoot]),
            'id'       => $addon->id_secure,
            'module'   => $moduleRoot,
            'version'  => $moduleMeta['version'] ?? null,
            'meta'     => $moduleMeta,
        ]);
    }

    public function doUpdate(Request $request, $productId)
    {
        $addon = Addon::where('product_id', $productId)
            ->where('source', 1)
            ->whereNotNull('purchase_code')
            ->first();

        if (!$addon) {
            return response()->json([
                'status'  => 0,
                'message' => __('Addon not found or not installed from marketplace.'),
            ]);
        }

        if ($addon->status == 0) {
            return response()->json([
                'status'  => 0,
                'message' => __('This module is deactivated. Please activate it before updating.'),
            ]);
        }

        $domain = preg_replace('/^www\./', '', $request->getHost());

        // 1. Request the Marketplace server for update info
        $response = Http::withoutVerifying()->post(self::API_BASE_URL . 'update', [
            'purchase_code'     => $addon->purchase_code,
            'product_id'        => $addon->product_id,
            'current_version'   => $addon->version,
            'domain'            => $domain,
            'website'           => route('home'),

        ]);

        if (!$response->ok() || ($response['status'] ?? 0) != 1) {
            return response()->json([
                'status'  => 0,
                'message' => $response['message'] ?? __('No update available.'),
            ]);
        }

        $downloadUrl   = $response['download_url'] ?? null;
        $latestVersion = $response['latest_version'] ?? null;
        $relativePath  = $response['install_path'] ?? null;
        $moduleName    = $response['module_name'] ?? null;
        $installPath   = $relativePath ? realpath( base_path($relativePath) ) : null;

        // Validate response fields
        if (
            empty($downloadUrl) || empty($latestVersion) ||
            empty($installPath) || empty($relativePath)
        ) {
            return response()->json([
                'status'  => 0,
                'message' => __('Missing install info from API response.')
            ]);
        }

        // 2. Download update zip using Guzzle
        $tempPath = storage_path('app/addons/tmp_' . \Str::random(8) . '.zip');
        try {
            \File::ensureDirectoryExists(dirname($tempPath));
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $guzzleResponse = $client->get($downloadUrl);

            if ($guzzleResponse->getStatusCode() !== 200) {
                throw new \Exception('Download failed with status code: ' . $guzzleResponse->getStatusCode());
            }

            file_put_contents($tempPath, $guzzleResponse->getBody()->getContents());
        } catch (\Exception $e) {
            \Log::error('[Addon Update] Download failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 0,
                'message' => __('Download failed: :error', ['error' => $e->getMessage()]),
            ]);
        }

        // 3. Extract to the correct install directory
        try {
            $zip = new \ZipArchive();
            if ($zip->open($tempPath) === TRUE) {
                \File::ensureDirectoryExists($installPath);
                $zip->extractTo($installPath);
                $zip->close();
            } else {
                \File::delete($tempPath);
                return response()->json([
                    'status'  => 0,
                    'message' => __('Unable to unzip addon.'),
                ]);
            }
        } catch (\Exception $e) {
            \File::delete($tempPath);
            \Log::error('[Addon Install] Unzip failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 0,
                'message' => __('Failed to extract addon: :error', ['error' => $e->getMessage()]),
            ]);
        }
        \File::delete($tempPath);

        // 4. Migrate if needed
        try {
            $migrationsDirs = [];

            if ($moduleName) {
                $migrationsDirs[] = base_path("Modules/{$moduleName}/Database/Migrations");
            }

            // luôn chạy migrations chính
            $migrationsDirs[] = base_path("database/migrations");

            foreach ($migrationsDirs as $dir) {
                if (!is_dir($dir)) continue;

                foreach (glob($dir . '/*.php') as $file) {
                    try {
                        $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                        $relative = str_replace('\\', '/', $relative);

                        \Artisan::call('migrate', [
                            '--path'          => $relative,
                            '--force'         => true,
                            '--no-interaction'=> true,
                        ]);

                        \Log::info("[Addon Update] Migrated: {$relative}");
                    } catch (\Exception $e) {
                        // ghi log nhưng bỏ qua lỗi, chạy tiếp file khác
                        \Log::warning("[Addon Update] Migration failed at {$file}: " . $e->getMessage());
                        continue;
                    }
                }
            }

            \Artisan::call('optimize:clear');
        } catch (\Exception $e) {
            \Log::warning('[Addon Update] Migration loop failed: ' . $e->getMessage());
        }

        // 5. Update version in addons table
        $addon->version = $latestVersion;
        $addon->changed = now()->timestamp;
        $addon->save();

        return response()->json([
            'status'  => 1,
            'message' => __('Addon updated successfully to version :ver', ['ver' => $latestVersion]),
            'version' => $latestVersion,
        ]);
    }

    public function destroy(Request $request, $id_secure)
    {
        $addon = Addon::where('id_secure', $id_secure)->first();

        if (!$addon) {
            return response()->json([
                'status'  => 0,
                'message' => __('Addon not found.'),
            ]);
        }

        if ($addon->is_main == 1) {
            return response()->json([
                'status'  => 0,
                'message' => __('You cannot delete main script'),
            ]);
        }

        $moduleName = $addon->module_name??'';

        try {
            // Delete the Laravel module by Artisan command
            if ($module = \Module::find($moduleName)) {
                \Artisan::call('module:delete', [
                    'module' => $moduleName,
                    '--force' => true,
                ]);

                // Remove directory manually if still exists (just in case)
                $modulePath = $module->getPath();
                if (\File::exists($modulePath)) {
                    \File::deleteDirectory($modulePath);
                }
            }
        } catch (\Exception $e) {
            \Log::warning("[Addon Destroy] Failed to delete module '{$moduleName}': " . $e->getMessage());
            return response()->json([
                'status'  => 0,
                'message' => __('Failed to delete the module: :error', ['error' => $e->getMessage()])
            ]);
        }

        $addon->delete();

        return response()->json([
            'status'  => 1,
            'message' => __('Addon has been permanently deleted.')
        ]);
    }

    public function active(Request $request, $id_secure)
    {
        $addon = Addon::where('id_secure', $id_secure)->first();
        if (!$addon) {
            return response()->json(['status' => 0, 'message' => __('Addon not found.')]);
        }

        if ($addon->is_main == 1) {
            return response()->json(['status' => 0, 'message' => __('You cannot active main script')]);
        }

        $moduleName = $addon->module_name ?? '';

        try {
            if ($module = \Module::find($moduleName)) {
            \Artisan::call('module:enable', ['module' => $moduleName]);
            \Artisan::call('module:migrate', ['module' => $moduleName]);
            $migrationsDir = $module->getPath().DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
            if (is_dir($migrationsDir)) {
                $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $migrationsDir);
                $relative = str_replace('\\', '/', $relative);

                \Artisan::call('migrate', [
                    '--path'          => $relative,
                    '--force'         => true,
                    '--no-interaction'=> true,
                ]);
            }
            \Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
            \Log::info("[{$moduleName}] migrate(all) output:\n".\Artisan::output());
            \Artisan::call('module:publish', ['module' => $moduleName]);
            \Artisan::call('optimize:clear');
        }

            $addon->status = 1;
            $addon->save();

            return response()->json(['status' => 1]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => __('Failed to activate the addon: :error', ['error' => $e->getMessage()]),
            ]);
        }
    }

    public function deactive(Request $request, $id_secure)
    {
        $addon = Addon::where('id_secure', $id_secure)->first();
        if (!$addon) {
            return response()->json([
                'status' => 0,
                'message' => __('Addon not found.'),
            ]);
        }

        if ($addon->is_main == 1) {
            return response()->json([
                'status'  => 0,
                'message' => __('You cannot deactive main script'),
            ]);
        }

        $moduleName = $addon->module_name??'';
        try {
            if ($module = \Module::find($moduleName)) {
                \Artisan::call('module:disable', ['module' => $moduleName]);
            }
            $addon->status = 0;
            $addon->save();

            return response()->json([
                'status' => 1,
            ]);
        } catch (\Exception $e) {
            \Log::warning("[Addon Deactivate] Failed: " . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => __('Failed to deactivate the addon: :error', ['error' => $e->getMessage()]),
            ]);
        }
    }

}