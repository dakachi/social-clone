<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\AIModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    protected Client $client;
    protected string $apiKey;
    protected array $cachedModels = [];

    protected array $fallbacks = [
        'text'   => 'claude-opus-4.1-20250805',
        'vision' => 'claude-sonnet-4-20250514',
    ];

    public function __construct()
    {
        $this->apiKey = (string) get_option("ai_claude_api_key", "");
        $this->client = new Client(['base_uri' => 'https://api.anthropic.com/v1/']);
    }

    /** ---------------- Helper: lấy model ---------------- */
    protected function getModel(string $category = 'text', ?string $default = null): string
    {
        $default ??= $this->fallbacks[$category] ?? 'claude-opus-4.1-20250805';

        if (empty($this->cachedModels)) {
            $this->cachedModels = array_keys($this->getModels());
        }

        $optionKey = "ai_claude_model_{$category}";
        $model     = get_option($optionKey, $default);

        if (!in_array($model, $this->cachedModels, true)) {
            $model = $default;
            try {
                DB::table('options')->updateOrInsert(
                    ['key' => $optionKey],
                    ['value' => $default, 'updated_at' => now()]
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to update default Claude model for {$category}: " . $e->getMessage());
            }
        }

        return $model;
    }

    /** ---------------- Lấy models khả dụng ---------------- */
    public function getModels(): array
    {
        try {
            $models = AIModel::query()
                ->where('provider', 'claude')
                ->where('is_active', 1)
                ->orderBy('category')
                ->orderBy('name')
                ->get(['model_key', 'name']);

            return $models->pluck('name', 'model_key')->toArray();
        } catch (\Throwable $e) {
            Log::error("Error fetching Claude models from DB: " . $e->getMessage());
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
            : [['role' => 'user', 'content' => $content]];

        return $this->sendRequest($model, $messages, $maxLength);
    }

    /** ---------------- Vision (Image + Text input) ---------------- */
    public function generateVision(
        string|array $prompt,
        array $options = [],
        string $category = 'vision'
    ): array {
        $model = $this->getModel($category);

        $content = is_array($prompt)
            ? $prompt
            : [["type" => "text", "text" => $prompt]];

        if (!empty($options['image'])) {
            $content[] = [
                "type"   => "image",
                "source" => [
                    "type"       => "base64",
                    "media_type" => $options['mimeType'] ?? "image/png",
                    "data"       => base64_encode($options['image']),
                ],
            ];
        }

        $messages = [['role' => 'user', 'content' => $content]];
        return $this->sendRequest($model, $messages, $options['max_tokens'] ?? 1024);
    }

    /** ---------------- Stubs (not supported) ---------------- */
    public function generateImage(string $prompt, array $options = [], string $category = 'image'): array
    {
        return $this->notSupported("image", $category);
    }

    public function generateVideo(string $prompt, array $options = [], string $category = 'video'): array
    {
        return $this->notSupported("video", $category);
    }

    public function generateEmbedding(string $prompt, array $options = [], string $category = 'embedding'): array
    {
        return $this->notSupported("embedding", $category);
    }

    public function textToSpeech(string $text, array $options = [], string $category = 'speech'): array
    {
        return $this->notSupported("speech", $category);
    }

    public function speechToText(string $filePath, array $options = [], string $category = 'speech_to_text'): array
    {
        return $this->notSupported("speech_to_text", $category);
    }

    public function generateAudio(string $filePath, array $options = [], string $category = 'audio'): array
    {
        return $this->speechToText($filePath, $options, $category);
    }

    /** ---------------- Request Anthropic API ---------------- */
    protected function sendRequest(string $model, array $messages, int $maxTokens = 1024): array
    {
        try {
            $response = $this->client->request('POST', 'messages', [
                'headers' => [
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type'      => 'application/json',
                ],
                'json' => [
                    'model'     => $model,
                    'max_tokens'=> $maxTokens,
                    'messages'  => $messages,
                ],
                'timeout' => 60,
            ]);

            $body = json_decode($response->getBody(), true);

            $promptTokens     = $body['usage']['input_tokens'] ?? 0;
            $completionTokens = $body['usage']['output_tokens'] ?? 0;
            $totalTokens      = $promptTokens + $completionTokens;

            return $this->successResponse(
                $model,
                [$body['content'][0]['text'] ?? ''],
                [
                    'promptTokens'     => $promptTokens,
                    'completionTokens' => $completionTokens,
                    'totalTokens'      => $totalTokens,
                ]
            );

        } catch (\Throwable $e) {
            dd($e);
            return $this->errorResponse($model, $e, 'text');
        }
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
        Log::error("Claude {$category} error with model {$model}: " . $e->getMessage());

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

    protected function notSupported(string $feature, string $category): array
    {
        return [
            'data'             => [],
            'promptTokens'     => 0,
            'completionTokens' => 0,
            'totalTokens'      => 0,
            'minutesUsed'      => 0,
            'model'            => $category,
            'error'            => "Claude does not support {$feature} generation",
        ];
    }
}
