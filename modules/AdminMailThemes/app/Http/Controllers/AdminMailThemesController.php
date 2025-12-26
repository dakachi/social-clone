<?php

namespace Modules\AdminMailThemes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminMailThemesController extends Controller
{
    public function index()
    {
        $modulePath = module('module_path');
        $themesPath = $modulePath . '/resources/views/themes';

        if (!File::exists($themesPath)) {
            File::makeDirectory($themesPath, 0777, true); // true để tạo đệ quy nhiều cấp
        }

        $themes = [];
        foreach (File::directories($themesPath) as $themeDir) {
            $slug = basename($themeDir);
            $jsonPath = $themeDir . '/theme.json';
            $info = File::exists($jsonPath) ? json_decode(File::get($jsonPath), true) : [];
            $themes[] = [
                'slug' => $slug,
                'info' => $info,
                'preview' => File::exists($themeDir.'/preview.png') ? url('modules/AdminMailThemes/resources/views/themes/'.$slug.'/preview.png') : text2img($slug),
            ];
        }

        return view('adminmailthemes::index',[
            "active" => get_option("mail_themes"),
            "themes" => $themes
        ]);
    }

    public function setDefault(Request $request)
    {
        $slug = $request->input("id");
        update_option('mail_themes', $slug);
        return response()->json([
            'status' => 1,
            'message' => __('Theme activated successfully!'),
            'active' => $slug,
        ]);
    }

}
