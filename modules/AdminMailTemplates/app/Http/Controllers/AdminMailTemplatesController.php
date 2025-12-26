<?php

namespace Modules\AdminMailTemplates\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminMailTemplatesController extends Controller
{
    public function index()
    {
        $allTemplates = \MailSender::getTemplates();
        return view('adminmailtemplates::index', [
            "allTemplates" => $allTemplates
        ]);
    }

    public function saveTemplateContent(Request $request)
    {
        $view = $request->input('view');
        $content = $request->input('content');

        if (!$view || !$content) {
            return response()->json([
                'status' => 0,
                'message' => __('Missing required fields.')
            ]);
        }

        // Tìm module chứa template
        $allTemplates = \MailSender::getTemplates();
        $matched = null;
        $matchedModule = null;

        foreach ($allTemplates as $module => $templates) {
            foreach ($templates as $tpl) {
                if ($tpl['view'] === $view) {
                    $matched = $tpl;
                    $matchedModule = $module;
                    break 2;
                }
            }
        }

        if (!$matched || !$matchedModule) {
            return response()->json([
                'status' => 0,
                'message' => __('Template not found.')
            ]);
        }

        // Đường dẫn file thực tế
        $modulePath = \Module::find($matchedModule)?->getPath();
        $viewPath = $modulePath . '/resources/views/' . $view . '.blade.php';

        if (!File::exists($viewPath)) {
            return response()->json([
                'status' => 0,
                'message' => __('Template file does not exist.')
            ]);
        }

        // Ghi nội dung vào file
        try {
            File::put($viewPath, $content);

            return response()->json([
                'status' => 1,
                'message' => __('Template saved successfully.')
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => __('Failed to save template: ') . $e->getMessage()
            ]);
        }
    }

}
