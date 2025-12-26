<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\AIModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    protected Client $client;
    protected string $apiKey;
    protected array $cachedModels = [];

    protected array $fallbacks = [
        'text'      => 'deepseek-chat',
        'reasoning' => 'deepseek-reasoner',
    ];

    public function __construct()
    {
        $this->apiKey = (string) get_option("ai_deepseek_api_key", "");
        $this->client = new Client([
            'base_uri' => 'https://api.deepseek.com/v1/',
        ]);
    }

    protected function getModel(string $category, ?string $default = null): string
    {
        $default ??= $this->fallbacks[$category] ?? 'deepseek-chat';

        if (empty($this->cachedModels)) {
            $this->cachedModels = array_keys($this->getModels());
        }

        $optionKey = "ai_deepseek_model_{$category}";
        $model     = get_option($optionKey, $default);

        if (!in_array($model, $this->cachedModels, true)) {
            $model = $default;
            try {
                DB::table('options')->updateOrInsert(
                    ['key' => $optionKey],
                    ['value' => $default, 'updated_at' => now()]
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to update default DeepSeek model for {$category}: " . $e->getMessage());
            }
        }

        return $model;
    }

    public function getModels(): array
    {
        try {
            $models = AIModel::query()
                ->where('provider', 'deepseek')
                ->where('is_active', 1)
                ->orderBy('category')
                ->orderBy('name')
                ->get(['model_key', 'name']);

            return $models->pluck('name', 'model_key')->toArray();
        } catch (\Throwable $e) {
            Log::error("Error fetching DeepSeek models from DB: " . $e->getMessage());
            return [];
        }
    }

    /** ---------------- Text ---------------- */
    public function generateText(
        string|array $content,
        int $maxLength,
        ?int $maxResult = null,
        string $category = 'text',
        array $options = []
    ): array {
        $model = $this->getModel($category);

        $messages = is_array($content)
            ? $content
            : [[
                "role"    => "user",
                "content" => $content,
            ]];

        try {
            $response = $this->client->request('POST', 'chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'      => $model,
                    'messages'   => $messages,
                    'max_tokens' => (int) $maxLength,
                    'temperature'=> $options['temperature'] ?? 0.7,
                    'top_p'      => $options['top_p'] ?? 0.95,
                    // 'n' => $maxResult ?? 1, // DeepSeek chưa chắc đã hỗ trợ nhiều completions
                ],
                'timeout' => 60,
            ]);

            $body = json_decode($response->getBody(), true);

            $result = [];
            if (!empty($body['choices'])) {
                foreach ($body['choices'] as $choice) {
                    $result[] = $choice['message']['content'] ?? '';
                }
            }

            return $this->successResponse($model, $result, [
                'promptTokens'     => $body['usage']['prompt_tokens'] ?? 0,
                'completionTokens' => $body['usage']['completion_tokens'] ?? 0,
                'totalTokens'      => $body['usage']['total_tokens'] ?? 0,
            ]);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    /** ---------------- Image ---------------- */
    public function generateImage(string $prompt, array $options = [], string $category = 'image'): array
    {
        return $this->errorResponse('deepseek-image', new \Exception("DeepSeek image generation not supported"), $category);
    }

    /** ---------------- Video ---------------- */
    public function generateVideo(string $prompt, array $options = [], string $category = 'video'): array
    {
        return $this->errorResponse('deepseek-video', new \Exception("DeepSeek video generation not supported"), $category);
    }

    /** ---------------- Vision ---------------- */
    public function generateVision(string|array $prompt, array $options = [], string $category = 'vision'): array
    {
        return $this->errorResponse('deepseek-vision', new \Exception("DeepSeek vision not supported"), $category);
    }

    /** ---------------- Embedding ---------------- */
    public function generateEmbedding(string $prompt, array $options = [], string $category = 'embedding'): array
    {
        return $this->errorResponse('deepseek-embedding', new \Exception("DeepSeek embedding not supported"), $category);
    }

    /** ---------------- Speech ---------------- */
    public function textToSpeech(string $text, array $options = [], string $category = 'speech'): array
    {
        return $this->errorResponse('deepseek-tts', new \Exception("DeepSeek TTS not supported"), $category);
    }

    public function speechToText(string $filePath, array $options = [], string $category = 'speech_to_text'): array
    {
        return $this->errorResponse('deepseek-stt', new \Exception("DeepSeek STT not supported"), $category);
    }

    public function generateAudio(string $filePath, array $options = [], string $category = 'audio'): array
    {
        return $this->speechToText($filePath, $options, $category);
    }

    /** ---------------- Helpers ---------------- */
    protected function successResponse(string $model, array $data, array $usage = [], float $minutesUsed = 0): array
    {
        return [
            'data'             => $data,
            'promptTokens'     => $usage['promptTokens'] ?? 0,
            'completionTokens' => $usage['completionTokens'] ?? 0,
            'totalTokens'      => $usage['totalTokens'] ?? 0,
            'minutesUsed'      => $minutesUsed,
            'model'            => $model,
            'error'            => null,
        ];
    }

    protected function errorResponse(string $model, \Throwable $e, string $category = ''): array
    {
        Log::error("DeepSeek {$category} error with model {$model}: " . $e->getMessage());

        return [
            'data'             => [],
            'promptTokens'     => 0,
            'completionTokens' => 0,
            'totalTokens'      => 0,
            'minutesUsed'      => 0,
            'model'            => $model,
            'error'            => $e->getMessage(),
        ];
    }
}
