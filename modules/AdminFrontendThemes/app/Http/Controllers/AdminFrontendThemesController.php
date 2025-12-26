<?php

namespace Modules\AdminFrontendThemes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AdminFrontendThemes\Services\FrontendThemeService;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class AdminFrontendThemesController extends Controller
{
    public function index()
    {
        $themes = app(FrontendThemeService::class)->all();
        $activeTheme = get_option('frontend_theme', env('THEME_FRONTEND'));
        return view('adminfrontendthemes::index', compact('themes', 'activeTheme'));
    }

    public function setDefault(Request $request)
    {
        $themeId = $request->input("id");
        $service = app(FrontendThemeService::class);
        if (!$service->get($themeId)) {
            return response()->json([
                'status' => 0,
                'message' => __('Theme does not exist or is unavailable.'),
            ]);
        }
        update_option('frontend_theme', $themeId);
        return response()->json([
            'status' => 1,
            'message' => __('Theme activated successfully!'),
        ]);
    }

    public function import(Request $request)
    {
        $files = $request->file('files');
        if (is_array($files)) {
            $file = $files[0];
        } else {
            $file = $files;
        }
        if (!$file || !$file->isValid() || $file->getClientOriginalExtension() !== 'zip') {
            return response()->json([
                'status' => 0,
                'message' => __('Invalid file.')
            ]);
        }

        $themePath = base_path('resources/themes/guest/');
        if (!file_exists($themePath)) {
            mkdir($themePath, 0775, true);
        }

        $zipName = uniqid('theme_', true) . '.zip';
        $tmpZipPath = storage_path('app/tmp/' . $zipName);
        $file->move(storage_path('app/tmp'), $zipName);

        $zip = new ZipArchive();
        if ($zip->open($tmpZipPath) === true) {
            $mainDir = $zip->getNameIndex(0);
            $mainDir = explode('/', $mainDir)[0];
            $extractPath = $themePath . $mainDir;
            if (file_exists($extractPath)) {
                $zip->close();
                unlink($tmpZipPath);
                return response()->json([
                    'status' => 0,
                    'message' => __('Theme already exists.')
                ]);
            }
            $zip->extractTo($themePath);
            $zip->close();
            unlink($tmpZipPath);

            // Check theme.json
            if (!file_exists($extractPath . '/theme.json')) {
                \File::deleteDirectory($extractPath);
                return response()->json([
                    'status' => 0,
                    'message' => __('theme.json not found in theme package.')
                ]);
            }

            return response()->json([
                'status' => 1,
                'message' => __('Theme imported successfully!'),
                'theme' => $mainDir
            ]);
        } else {
            if (file_exists($tmpZipPath)) unlink($tmpZipPath);
            return response()->json([
                'status' => 0,
                'message' => __('Could not open the zip file.')
            ]);
        }
    }


}
