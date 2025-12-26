<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\AIModel;

class AIService
{
    protected $openAIService;
    protected $deepSeekService;
    protected $geminiService;
    protected $claudeService;

    public function __construct(
        OpenAIService   $openAIService,
        DeepSeekService $deepSeekService,
        GeminiService   $geminiService,
        ClaudeService   $claudeService
    ) {
        $this->openAIService   = $openAIService;
        $this->deepSeekService = $deepSeekService;
        $this->geminiService   = $geminiService;
        $this->claudeService   = $claudeService;
    }

    /**
     * Xử lý request AI theo platform + category
     */
    public function process($content, string $category = 'text', array $options = [], int $teamId = 0): array
    {
        $maxLength = get_option("ai_max_output_lenght", 1000);
        
        switch ($category) {
            case 'image':
                $platform  = get_option("ai_platform_image", "openai");
                break;
            
            default:
                $platform  = get_option("ai_platform", "openai");
                break;
        }

        // Kiểm tra quota
        $quota = \Credit::checkQuota($teamId);
        if (!$quota['can_use']) {
            throw new \Exception($quota['message']);
        }

        $service = $this->getService($platform);

        // Dispatch theo category
        $response = match ($category) {
            'text'           => $service->generateText($content, $maxLength, $options['maxResult'] ?? null, $category),
            'image'          => $service->generateImage($content, $options, $category),
            'video'          => $service->generateVideo($content, $options, $category),
            'speech'         => $service->textToSpeech($content, $options, $category),
            'speech_to_text' => $service->speechToText($content, $options, $category),
            'audio'          => $service->generateAudio($content, $options, $category),
            'embedding'      => $service->generateEmbedding($content, $options, $category),
            'vision'         => $service->generateVision($content, $options, $category),
            default          => throw new \Exception("Category {$category} not supported"),
        };

        // Tính credits
        $this->trackCredits($platform, $category, $response, $teamId);

        return $response;
    }

    /**
     * Lấy service theo platform
     */
    protected function getService(string $platform)
    {
        return match ($platform) {
            'openai'   => $this->openAIService,
            'deepseek' => $this->deepSeekService,
            'gemini'   => $this->geminiService,
            'claude'   => $this->claudeService,
            default    => throw new \Exception("Platform {$platform} not supported"),
        };
    }

    /**
     * Tính và lưu credits
     */
    protected function trackCredits(string $platform, string $category, array $response, int $teamId): void
    {
        // Nếu speech_to_text -> tính theo phút
        if ($category === 'speech_to_text' && isset($response['minutesUsed'])) {
            $minutes = $response['minutesUsed'] ?? 0;
            $creditsUsed = \Credit::convertMinutesToCredits($response['model'] ?? '', $minutes);
            \Credit::trackUsage($creditsUsed, 'ai_minutes', $platform, $teamId);
            return;
        }

        // Các category token-based
        $tokenCategories = ['text', 'embedding', 'vision', 'speech'];
        if (in_array($category, $tokenCategories)) {
            $tokensUsed  = $response['totalTokens'] ?? 0;
            $creditsUsed = \Credit::convertToCredits($response['model'] ?? '', $tokensUsed);
            \Credit::trackUsage($creditsUsed, 'ai_words', $platform, $teamId);
        }

        // Image/video có thể tính riêng nếu muốn (theo số lượng hoặc dung lượng)
    }

    /**
     * Lấy models khả dụng theo provider từ service
     */
    public function getModels(string $platform): array
    {
        return $this->getService($platform)->getModels();
    }

    /**
     * Mô tả model (lấy từ DB)
     */
    public function getModelDescription(string $platform, string $model): string
    {
        $models = $this->getAvailableModels($platform);
        foreach ($models as $category => $items) {
            if (isset($items[$model])) {
                return $items[$model];
            }
        }
        return __('Unknown model');
    }

    /**
     * Danh sách platforms khả dụng
     */
    public function getPlatforms(): array
    {
        return [
            "openai"   => __("OpenAI"),
            "deepseek" => __("DeepSeek"),
            "gemini"   => __("Gemini"),
            "claude"   => __("Claude"),
        ];
    }

    /**
     * Danh sách categories
     */
    public function getCategory(?string $name = null)
    {
        $labels = [
            'text'           => __('Text'),
            'image'          => __('Image'),
            'video'          => __('Video'),
            'audio'          => __('Audio'),
            'vision'         => __('Vision (image+text input)'),
            'speech'         => __('Speech (Text to Speech)'),
            'speech_to_text' => __('Speech to Text'),
            'embedding'      => __('Embedding'),
        ];

        return $name ? ($labels[$name] ?? null) : $labels;
    }

    public function getLatestModels(): array
    {
        $url = 'https://stackposts.com/ai_models.json';
        $response = Http::timeout(30)->get($url);

        if ($response->failed()) {
            throw new \Exception("Failed to fetch models");
        }

        $models = json_decode($response->body(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error("Invalid JSON from {$url}: " . json_last_error_msg(), [
                'body' => $response->body()
            ]);
            throw new \Exception("Invalid JSON format");
        }

        return $models;
    }

    public function syncModels(array $models): void
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
            'speech'    => ($m['type'] ?? null) === 'tts' ? 'tts' : 'stt',
            'image'     => 'image',
            'video'     => 'video',
            default     => ($m['provider'] === 'openai' && str_starts_with($m['model_key'], 'gpt-5'))
                ? 'responses'
                : 'chat',
        };
    }

    public function getAvailableModels(string $platform): array
    {
        try {
            $models = AIModel::query()
                ->where('provider', $platform)
                ->where('is_active', 1)
                ->orderBy('category')
                ->orderBy('name')
                ->get(['model_key', 'name', 'category', 'api_type']);

            if ($models->isEmpty()) {
                return [];
            }

            return $models
                ->groupBy('category')
                ->mapWithKeys(function ($items, $category) {
                    return [
                        $category => $items->mapWithKeys(function ($item) {
                            return [
                                $item->model_key => [
                                    'name'     => $item->name,
                                    'api_type' => $item->api_type,
                                ]
                            ];
                        })->toArray()
                    ];
                })
                ->toArray();

        } catch (\Throwable $e) {
            \Log::error("Error fetching AI models from DB for {$platform}: " . $e->getMessage());
            return [];
        }
    }

    public function bk_getAvailableModels($platform)
    {
        return match ($platform) {
            'openai'   => [
                "gpt-4.5-turbo"           => __("GPT-4.5 Turbo - (Newest, fastest, most capable general-purpose model for all text tasks)"),
                "gpt-4o"                  => __("GPT-4o - (Unified model for text, vision, audio; excellent general text and reasoning)"),
                "gpt-4o-mini"             => __("GPT-4o Mini - (Compact, efficient, suitable for scalable text generation)"),
                "gpt-4-turbo"             => __("GPT-4 Turbo - (Faster, lower cost, great for production text tasks)"),
                "gpt-4"                   => __("GPT-4 - (Advanced, high-quality general text generation)"),
                "gpt-3.5-turbo"           => __("GPT-3.5 Turbo - (Cost-effective, fast, general text and chat)"),
                "gpt-3.5-turbo-instruct"  => __("GPT-3.5 Turbo Instruct - (Instruction-following, suitable for structured tasks)"),
            ],
            'deepseek' => [
                "deepseek-chat"     => __("DeepSeek Chat - (General-purpose conversational and text generation model)"),
                "deepseek-reasoner" => __("DeepSeek Reasoner - (Advanced reasoning model for logical and complex tasks)"),
            ],
            'gemini'   => [
                "gemini-2.5-flash"          => __("Gemini 2.5 Flash - (Newest, fastest, accurate, optimized for general real-time text tasks)"),
                "gemini-2.5-flash-lite"     => __("Gemini 2.5 Flash Lite - (Ultra fast, low-cost, suitable for large volume and latency-sensitive text)"),
                "gemini-2.5-pro"            => __("Gemini 2.5 Pro - (Advanced reasoning, best-in-class for complex text and multimodal tasks)"),
                "gemini-2.0-flash"          => __("Gemini 2.0 Flash - (Previous gen, versatile, multimodal, fast text generation)"),
                "gemini-2.0-flash-lite"     => __("Gemini 2.0 Flash Lite - (Optimized for cost efficiency, low latency text tasks)"),
                "gemini-2.0-pro"            => __("Gemini 2.0 Pro - (Improved capabilities for native tool use and general text)"),
                "gemini-1.5-pro"            => __("Gemini 1.5 Pro - (Good for reasoning and complex text tasks)"),
                "gemini-1.5-flash"          => __("Gemini 1.5 Flash - (General fast text, prior generation)"),
                "gemini-1.5-flash-8b"       => __("Gemini 1.5 Flash 8B - (Optimized for high-volume, basic text tasks)"),
                "gemini-2.0-flash-thinking" => __("Gemini 2.0 Flash Thinking - (Specialized for complex reasoning)"),
            ],
            'claude'   => [
                "claude-opus-4.1-20250805"   => __("Claude Opus 4.1 (most powerful, long context)"),
                "claude-opus-4-20250514"     => __("Claude Opus 4 (advanced reasoning, top-tier)"),
                "claude-sonnet-4-20250514"   => __("Claude Sonnet 4 (balanced, versatile)"),
                "claude-3.7-sonnet-20250219" => __("Claude Sonnet 3.7 (hybrid reasoning)"),
                "claude-3.5-haiku-20241022"  => __("Claude Haiku 3.5 (fast, affordable)"),
                "claude-3-haiku-20240307"    => __("Claude Haiku 3 (cheapest, ultra-fast basic tasks)"),
            ],
            default    => throw new \Exception("Platform {$platform} not supported"),
        };
    }
}
