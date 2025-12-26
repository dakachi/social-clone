<?php

namespace App\Services;

use OpenAI;
use App\Models\AIModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $client;
    protected string $apiKey;
    protected array $cachedModels = [];

    /**
     * Default fallbacks by category
     */
    protected array $fallbacks = [
        'text'           => 'gpt-4o-mini',
        'image'          => 'gpt-image-1',
        'video'          => 'sora-1',
        'speech'         => 'gpt-4o-mini-tts',
        'speech_to_text' => 'whisper-1',
        'embedding'      => 'text-embedding-3-small',
        'vision'         => 'gpt-4o-mini',
    ];

    public function __construct()
    {
        $this->apiKey = (string) get_option("ai_openai_api_key", "");
        $this->client = OpenAI::client($this->apiKey);
    }

    /**
     * Get model key by category with fallback
     */
    protected function getModel(string $category, ?string $default = null): string
    {
        $default ??= $this->fallbacks[$category] ?? 'gpt-4o-mini';

        if (empty($this->cachedModels)) {
            $this->cachedModels = array_keys($this->getModels());
        }

        $optionKey = "ai_openai_model_{$category}";
        $model     = get_option($optionKey, $default);

        if (!in_array($model, $this->cachedModels, true)) {
            $model = $default;
            try {
                DB::table('options')->updateOrInsert(
                    ['key' => $optionKey],
                    ['value' => $default, 'updated_at' => now()]
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to update default AI model for {$category}: " . $e->getMessage());
            }
        }

        return $model;
    }

    /**
     * Get active OpenAI models from DB
     */
    public function getModels(): array
    {
        try {
            $models = AIModel::query()
                ->where('provider', 'openai')
                ->where('is_active', 1)
                ->orderBy('category')
                ->orderBy('name')
                ->get(['model_key', 'name']);

            return $models->pluck('name', 'model_key')->toArray();
        } catch (\Throwable $e) {
            Log::error("Error fetching AI models from DB: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate text (chat or responses API)
     */
    public function generateText(string|array $prompt, int $maxLength, int $maxResult = 1, string $category = 'text'): array
    {
        $modelKey = $this->getModel($category);
        $aiModel  = AIModel::where('model_key', $modelKey)->first();
        $apiType  = $aiModel->api_type ?? 'chat';

        $messages = is_array($prompt)
            ? $prompt
            : [['role' => 'user', 'content' => $prompt]];

        try {
            if ($apiType === 'responses') {
                $response = $this->client->responses()->create([
                    'model'             => $modelKey,
                    'input'             => $prompt,
                    'max_output_tokens' => $maxLength,
                ]);

                $result = !empty($response->outputText)
                    ? [trim($response->outputText)]
                    : $this->extractResponsesOutput($response);

                return $this->successResponse($modelKey, $result, $response->usage ?? null);
            }

            // Chat API
            $response = $this->client->chat()->create([
                'model'                 => $modelKey,
                'messages'              => $messages,
                'max_completion_tokens' => $maxLength,
                'n'                     => $maxResult,
            ]);

            $result = [];
            foreach ($response->choices ?? [] as $choice) {
                if (!empty($choice->message->content)) {
                    $result[] = trim($choice->message->content);
                }
            }

            return $this->successResponse($modelKey, $result, $response->usage ?? null);

        } catch (\Throwable $e) {
            return $this->errorResponse($modelKey, $e, $category);
        }
    }

    protected function extractResponsesOutput($response): array
    {
        $result = [];
        foreach ($response->output ?? [] as $out) {
            foreach ($out->content ?? [] as $c) {
                if (($c->type ?? null) === 'output_text') {
                    $result[] = trim($c->text ?? '');
                }
            }
        }
        return $result;
    }

    /**
     * Generate image
     */
    public function generateImage(string $prompt, array $options = [], string $category = 'image'): array
    {
        $model = $this->getModel($category);
        $size  = $options['size'] ?? "1024x1024";
        $n     = $options['n'] ?? 1;

        try {
            $response = $this->client->images()->create([
                'model'  => $model,
                'prompt' => $prompt,
                'size'   => $size,
                'n'      => $n,
            ]);

            $images = [];
            foreach ($response->data ?? [] as $img) {
                if (!empty($img->url)) {
                    $images[] = [
                        'url'      => $img->url,
                        'mimeType' => 'image/png',
                    ];
                } elseif (!empty($img->b64_json)) {
                    $images[] = [
                        'b64_json' => $img->b64_json,
                        'mimeType' => 'image/png',
                    ];
                }
            }

            if (empty($images)) {
                throw new \Exception("Model {$model} did not return any image data.");
            }

            return $this->successResponse($model, $images);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    /**
     * Video (placeholder)
     */
    public function generateVideo(string $prompt, array $options = [], string $category = 'video'): array
    {
        $model = $this->getModel($category);
        return $this->errorResponse($model, new \Exception('OpenAI video generation not supported yet.'), $category);
    }

    /**
     * Speech-to-Text
     */
    public function speechToText(string $filePath, array $options = [], string $category = 'speech_to_text'): array
    {
        $model = $this->getModel($category);

        try {
            $response = $this->client->audio()->transcriptions()->create([
                'model' => $model,
                'file'  => fopen($filePath, 'r'),
            ]);

            $minutesUsed = $this->getAudioDurationMinutes($filePath);
            return $this->successResponse($model, [$response->text ?? ''], null, $minutesUsed);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    protected function getAudioDurationMinutes(string $filePath): float
    {
        try {
            if (class_exists(\FFMpeg\FFProbe::class)) {
                $ffprobe = \FFMpeg\FFProbe::create();
                $seconds = (int) $ffprobe->format($filePath)->get('duration');
                return round($seconds / 60, 2);
            }
        } catch (\Throwable $e) {
            Log::warning("Cannot get audio duration: " . $e->getMessage());
        }
        return 0;
    }

    /**
     * Text-to-Speech
     */
    public function textToSpeech(string $text, array $options = [], string $category = 'speech'): array
    {
        $model = $this->getModel($category);
        $voice = $options['voice'] ?? 'alloy';

        try {
            $response = $this->client->audio()->speech()->create([
                'model' => $model,
                'voice' => $voice,
                'input' => $text,
            ]);

            return $this->successResponse($model, [$response->toStream() ?? null]);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    /**
     * Embeddings
     */
    public function generateEmbedding(string $prompt, array $options = [], string $category = 'embedding'): array
    {
        $model = $this->getModel($category);

        try {
            $response = $this->client->embeddings()->create([
                'model' => $model,
                'input' => $prompt,
            ]);

            return $this->successResponse(
                $model,
                $response->data[0]->embedding ?? [],
                $response->usage ?? null
            );

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    /**
     * Vision (text + optional image)
     */
    public function generateVision(array|string $prompt, array $options = [], string $category = 'vision'): array
    {
        $modelKey = $this->getModel($category);
        $aiModel  = AIModel::where('model_key', $modelKey)->first();
        $apiType  = $aiModel->api_type ?? 'chat';

        $messages = is_array($prompt) ? $prompt : [[
            "role"    => "user",
            "content" => array_filter([
                ["type" => "text", "text" => $prompt],
                !empty($options['image'])
                    ? ["type" => "image_url", "image_url" => ["url" => $options['image']]]
                    : null,
            ]),
        ]];

        try {
            if ($apiType === 'responses') {
                $input = [[
                    "role"    => "user",
                    "content" => array_filter([
                        ["type" => "input_text", "text" => $prompt],
                        !empty($options['image'])
                            ? ["type" => "input_image", "image_url" => ["url" => $options['image']]]
                            : null,
                    ]),
                ]];

                $response = $this->client->responses()->create([
                    'model' => $modelKey,
                    'input' => $input,
                ]);

                $result = $this->extractResponsesOutput($response);
                return $this->successResponse($modelKey, $result, $response->usage ?? null);
            }

            $response = $this->client->chat()->create([
                'model'    => $modelKey,
                'messages' => $messages,
            ]);

            $result = array_map(fn($c) => $c->message->content ?? '', $response->choices ?? []);
            return $this->successResponse($modelKey, $result, $response->usage ?? null);

        } catch (\Throwable $e) {
            return $this->errorResponse($modelKey, $e, $category);
        }
    }

    /**
     * Helpers
     */
    protected function errorResponse(string $model, \Throwable $e, string $category = ''): array
    {
        Log::error("OpenAI {$category} error with model {$model}: " . $e->getMessage());

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

    protected function successResponse(string $model, array $data, $usage = null, float $minutesUsed = 0): array
    {
        if (is_object($usage) && method_exists($usage, 'toArray')) {
            $usage = $usage->toArray();
        }

        $usageArr = [
            'promptTokens'     => $usage['prompt_tokens']     ?? 0,
            'completionTokens' => $usage['completion_tokens'] ?? 0,
            'totalTokens'      => $usage['total_tokens']      ?? 0,
        ];

        return [
            'data'             => $data,
            'promptTokens'     => $usageArr['promptTokens'],
            'completionTokens' => $usageArr['completionTokens'],
            'totalTokens'      => $usageArr['totalTokens'],
            'minutesUsed'      => $minutesUsed,
            'model'            => $model,
            'error'            => null,
        ];
    }
}
