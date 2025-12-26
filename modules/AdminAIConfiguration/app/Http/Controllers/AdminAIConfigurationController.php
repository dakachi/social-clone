<?php

namespace Modules\AdminAIConfiguration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AIModel;
use App\Services\AIService;

class AdminAIConfigurationController extends Controller
{
    public function index()
    {
        // Danh sách provider hiển thị
        $providers = [
            'openai'   => 'OpenAI',
            'gemini'   => 'Gemini AI',
            'deepseek' => 'Deepseek AI',
            'claude'   => 'Claude AI',
        ];

        // Thứ tự category
        $categoryOrder  = ['text','image','video','audio','vision','speech','embedding'];
        $categoryLabels = [
            'text'      => __('Text'),
            'image'     => __('Image'),
            'video'     => __('Video'),
            'audio'     => __('Audio'),
            'vision'    => __('Vision (image+text input)'),
            'speech'    => __('Speech'),
            'embedding' => __('Embedding'),
        ];

        // Lấy tất cả models active
        $rawModels = AIModel::active()
            ->orderBy('provider')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Gom nhóm provider + category
        $models = $rawModels->groupBy(['provider', 'category']);

        // Lấy danh sách platform theo category (chỉ những provider có model active trong category đó)
        $platformsByCategory = AIModel::active()
            ->select('provider','category')
            ->distinct()
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->pluck('provider')->unique()->values()->toArray();
            })
            ->toArray();

        return view('adminaiconfiguration::index', [
            'providers'          => $providers,
            'categoryOrder'      => $categoryOrder,
            'categoryLabels'     => $categoryLabels,
            'models'             => $models,
            'platformsByCategory'=> $platformsByCategory,
        ]);
    }

    /**
     * Import models từ server (JSON build-in hoặc provider fetcher).
     */
    public function importAll(AIService $aiService)
    {
        try {
            $models = $aiService->getLatestModels();
            $this->syncModels($models);

            return response()->json([
                'status'  => 1,
                'message' => __('AI models updated and imported successfully!'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import models từ file JSON do khách hàng upload.
     */
    public function importJson(Request $request)
    {
        try {
            $request->validate([
                'json_file' => 'required|file|mimes:json,txt',
            ]);

            $jsonContent = file_get_contents($request->file('json_file')->getRealPath());
            $models = json_decode($jsonContent, true);

            if (!is_array($models)) {
                throw new \Exception(__('Invalid JSON format.'));
            }

            $this->syncModels($models);

            return response()->json([
                'status'  => 1,
                'message' => __('AI models imported successfully from JSON file!'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function syncModels(array $models): void
    {
        $importedIds = [];

        foreach ($models as $m) {
            $aiModel = AIModel::updateOrCreate(
                [
                    'provider'  => $m['provider'],
                    'model_key' => $m['model_key'],
                    'category'  => $m['category'] ?? 'text',
                ],
                [
                    'name'       => $m['name']      ?? $m['model_key'],
                    'type'       => $m['type']      ?? null,
                    'is_active'  => $m['is_active'] ?? true,
                    'api_type'   => $m['api_type'] ?? $this->guessApiType($m),
                    'api_params' => $m['api_params'] ?? null,
                    'meta'       => $m['meta']      ?? null,
                ]
            );

            if (empty($aiModel->id_secure)) {
                $aiModel->id_secure = rand_string(32);
                $aiModel->save();
            }

            $importedIds[] = $aiModel->id;
        }

        if (!empty($importedIds)) {
            AIModel::whereNotIn('id', $importedIds)->delete();
        }
    }

    protected function guessApiType(array $m): string
    {
        return match($m['category'] ?? 'text') {
            'embedding' => 'embeddings',
            'speech'    => $m['type'] === 'tts' ? 'tts' : 'stt',
            'image'     => 'image',
            'video'     => 'video',
            default     => ($m['provider'] === 'openai' && str_starts_with($m['model_key'], 'gpt-5'))
                            ? 'responses'
                            : 'chat',
        };
    }
}
