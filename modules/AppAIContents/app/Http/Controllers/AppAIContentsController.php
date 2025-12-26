<?php

namespace Modules\AppAIContents\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use AI;

class AppAIContentsController extends Controller
{
    protected string $templateTable = "ai_templates";
    protected string $categoryTable = "ai_categories";

    public function index()
    {
        return view('appaicontents::index');
    }

    public function categories(Request $request)
    {
        $categories = DB::table($this->categoryTable)
            ->where("status", 1)
            ->get();

        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::categories', [
                "categories" => $categories,
            ])->render()
        ]);
    }

    public function templates(Request $request)
    {
        $category = DB::table($this->categoryTable)
            ->where([
                "id_secure" => $request->id,
                "status"    => 1,
            ])->first();

        if (empty($category)) {
            return ms([
                "status"  => 0,
                "message" => __("Category does not exist")
            ]);
        }

        $templates = DB::table($this->templateTable)
            ->where([
                "cate_id" => $category->id,
                "status"  => 1
            ])->get();

        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::templates', [
                "category"  => $category,
                "templates" => $templates,
            ])->render()
        ]);
    }

    public function process(Request $request, string $page = "default")
    {
        $prompt     = trim($request->prompt ?? '');
        $aiOptions  = $request->ai_options ?? [];

        if ($prompt === '') {
            return ms([
                "status"  => "error",
                "message" => __("Please enter your prompt")
            ]);
        }

        $language     = $aiOptions['language']      ?? 'en-US';
        $maxLength    = (int) ($aiOptions['max_length'] ?? 100);
        $toneOfVoice  = $aiOptions['tone_of_voice'] ?? 'Friendly';
        $creativity   = $aiOptions['creativity']    ?? 0.5;
        $hashtags     = (int) ($aiOptions['hashtags'] ?? 0);
        $maxResult    = (int) ($aiOptions['number_result'] ?? 3);

        $maxResult = max(1, min($maxResult, 10)); // đảm bảo 1–10

        // Build prompt content
        $content = $this->buildPrompt($prompt, $language, $maxLength, $toneOfVoice, $creativity, $hashtags);

        try {
            $result = AI::process($content, 'text', [
                'maxResult' => $maxResult
            ]);
        } catch (\Throwable $e) {
            return ms([
                "status"  => 0,
                "message" => $e->getMessage(),
            ]);
        }

        $view = $page === 'popup' ? 'popup_result' : 'result';

        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::' . $view, [
                "result" => $result['data'] ?? [],
            ])->render()
        ]);
    }

    public function popupAIContent()
    {
        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::popup')->render()
        ]);
    }

    public function createContent(Request $request)
    {
        $content = trim($request->content ?? '');

        if ($content === '') {
            return ms([
                "status"  => "error",
                "message" => __("Please enter your prompt")
            ]);
        }

        try {
            $result = AI::process($content, 'text', ['maxResult' => 1]);

            return ms([
                "status" => 1,
                "data"   => $result['data'][0] ?? ''
            ]);
        } catch (\Throwable $e) {
            return ms([
                "status"  => 0,
                "message" => $e->getMessage(),
            ]);
        }
    }

    /** ---------------- Helper ---------------- */
    protected function buildPrompt(
        string $prompt,
        string $language,
        int $maxLength,
        string $toneOfVoice,
        float $creativity,
        int $hashtags = 0
    ): string {
        if ($hashtags > 0) {
            return "Create a paragraph about the content '{$prompt}' including {$hashtags} hashtags at the end of each paragraph with a maximum of {$maxLength} characters. Creativity is {$creativity} between 0 and 1. Use the {$language} language. Tone of voice must be {$toneOfVoice}.";
        }

        return "Create a paragraph about the content '{$prompt}' with a maximum of {$maxLength} words. Creativity is {$creativity} between 0 and 1. Use the {$language} language. Tone of voice must be {$toneOfVoice}.";
    }
}
