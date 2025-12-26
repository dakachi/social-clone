<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\AIModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;

class GeminiService
{
    protected Client $client;
    protected string $apiKey;
    protected array $cachedModels = [];

    protected array $fallbacks = [
        'text'           => 'gemini-2.5-flash',
        'image'          => 'gemini-2.5-pro',
        'video'          => 'gemini-2.5-pro',
        'vision'         => 'gemini-2.5-pro',
        'embedding'      => 'gemini-embedding',
        'speech'         => 'gemini-tts',
        'speech_to_text' => 'gemini-stt',
        'audio'          => 'gemini-stt',
    ];

    public function __construct()
    {
        $this->apiKey = (string) get_option("ai_gemini_api_key", "");
        // Use the general Gemini API endpoint
        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
        ]);
    }

    /**
     * Gets the configured or default model key for a specific category.
     */
    protected function getModel(string $category, ?string $default = null): string
    {
        $default ??= $this->fallbacks[$category] ?? 'gemini-2.5-flash';

        if (empty($this->cachedModels)) {
            $this->cachedModels = array_keys($this->getModels());
        }

        $optionKey = "ai_gemini_model_{$category}";
        $model     = get_option($optionKey, $default);

        if (!in_array($model, $this->cachedModels, true)) {
            $model = $default;
            try {
                DB::table('options')->updateOrInsert(
                    ['key' => $optionKey],
                    ['value' => $default, 'updated_at' => now()]
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to update default Gemini model for {$category}: " . $e->getMessage());
            }
        }

        return $model;
    }

    /**
     * Retrieves a list of active models from the database.
     */
    public function getModels(): array
    {
        try {
            $models = AIModel::query()
                ->where('provider', 'gemini')
                ->where('is_active', 1)
                ->orderBy('category')
                ->orderBy('name')
                ->get(['model_key', 'name']);

            return $models->pluck('name', 'model_key')->toArray();
        } catch (\Throwable $e) {
            Log::error("Error fetching Gemini models from DB: " . $e->getMessage());
            return [];
        }
    }

    // --- Core API Helpers ---

    /**
     * General helper to send requests to the Gemini API (:generateContent endpoint).
     */
    protected function sendGenerateContentRequest(string $model, array $payload, array $generationConfig = []): array
    {
        $payload['generationConfig'] = $generationConfig;
        
        // Remove empty config to avoid API errors if config is empty
        if (empty($payload['generationConfig'])) {
            unset($payload['generationConfig']);
        }

        return $this->makeAPICall($model, "models/{$model}:generateContent", $payload);
    }
    
    /**
     * General helper to send requests to a custom API endpoint (e.g., :predict for Imagen).
     */
    protected function makeAPICall(string $model, string $endpoint, array $payload): array
    {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'query'   => ['key' => $this->apiKey],
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode($payload),
                'timeout' => 60,
            ]);

            $body = json_decode($response->getBody(), true);
            return $body;

        } catch (ClientException $e) {
            $response  = $e->getResponse();
            $errorBody = $response ? (string)$response->getBody() : null;
            $message   = $e->getMessage();

            // Parse JSON error to get detailed message
            if ($errorBody) {
                $decoded = json_decode($errorBody, true);
                if (isset($decoded['error']['message'])) {
                    $message = $decoded['error']['message'];
                }
            }

            Log::error("Gemini API Client Error", [
                'model'   => $model,
                'status'  => $response?->getStatusCode(),
                'body'    => $errorBody,
            ]);

            throw new \Exception($message, $e->getCode(), $e);

        } catch (\Throwable $e) {
            Log::error("Gemini API Fatal Error", [
                'model' => $model,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // --- Text Generation ---

    /** * Generates text content using a Gemini model.
     */
    public function generateText(
        string|array $content,
        int $maxLength,
        ?int $maxResult = null,
        string $category = 'text',
        array $options = []
    ): array {
        $model = $this->getModel($category);

        $parts = is_array($content) ? $content : [["text" => $content]];
        $payload = [
            "contents" => [["parts" => $parts]]
        ];

        $generationConfig = [
            'candidate_count'   => $maxResult ?? 1,
            'max_output_tokens' => $maxLength,
            'temperature'       => $options['temperature'] ?? 0.7,
            'top_p'             => $options['top_p'] ?? 0.95,
        ];
        
        try {
            $body = $this->sendGenerateContentRequest($model, $payload, $generationConfig);

            $result = [];
            if (!empty($body['candidates'])) {
                foreach ($body['candidates'] as $candidate) {
                    $result[] = $candidate['content']['parts'][0]['text'] ?? '';
                }
            }

            $usage = $body['usageMetadata'] ?? [];
            return $this->successResponse(
                $model,
                $result,
                [
                    'promptTokens'     => $usage['promptTokenCount'] ?? 0,
                    'completionTokens' => $usage['candidatesTokenCount'] ?? 0,
                    'totalTokens'      => $usage['totalTokenCount'] ?? 0,
                ]
            );

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    // --- Image Generation (DALL-E style) ---

    /**
     * Generates images using either Imagen or Gemini Image models.
     */
    public function generateImage(string $prompt, array $options = [], string $category = 'image'): array
    {
        $model = $options['model'] ?? $this->getModel($category);

        try {
            if (str_starts_with($model, 'imagen-')) {
                // Use dedicated logic for Imagen (Vertex AI :predict endpoint)
                return $this->generateWithImagen($model, $prompt, $options);
            }

            if (str_contains($model, 'flash-image') || str_contains($model, 'gemini-')) {
                // Use dedicated logic for Gemini Image (:generateContent endpoint)
                return $this->generateWithGeminiImage($model, $prompt, $options);
            }

            // Fallback for unsupported model
            return $this->errorResponse($model, new \Exception("Unsupported image model: {$model}"), $category);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    /**
     * Handles image generation for Imagen models (Vertex AI - :predict).
     */
    protected function generateWithImagen(string $model, string $prompt, array $options): array
    {
        $sampleCount = $options['count'] ?? 1;
        $imageSize = $options['size'] ?? "1024x1024";

        $payload = [
            "instances" => [
                [
                    "prompt" => $prompt
                ]
            ],
            "parameters" => [
                "sampleCount" => $sampleCount,
                "imageSize"   => $imageSize
            ]
        ];

        try {
            $body = $this->makeAPICall($model, "models/{$model}:predict", $payload);

            $images = [];
            foreach ($body['predictions'] ?? [] as $pred) {
                if (!empty($pred['bytesBase64Encoded'])) {
                    $images[] = [
                        'b64_json' => $pred['bytesBase64Encoded'],
                        'mimeType' => 'image/png',
                    ];
                }
            }

            return $this->successResponse($model, $images);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, 'image');
        }
    }

    /**
     * Handles image generation for Gemini Image models (:generateContent endpoint).
     * FIX: Uses 'generationConfig' instead of 'config' in the payload.
     */
    protected function generateWithGeminiImage(string $model, string $prompt, array $options = []): array
    {
        // --- 1. Define Expanded Styles & Tones for Better Results ---
        $defaultStyles = [
            'photorealistic, 8k professional photograph, cinematic lighting', // Enhanced realistic photography
            'minimalist flat illustration, vector graphic style', // Modern flat illustration
            'hyper-detailed 3D render, subsurface scattering', // Enhanced 3D render
            'Japanese Ukiyo-e woodblock print style', // Unique artistic style
            'cinematic digital painting, moody colors', // Enhanced digital art
            'synthwave aesthetic, neon glow', // Modern concept art
            'oil painting, impasto texture, dramatic brushstrokes', // Classical art
        ];

        $defaultTones = [
            'professional, high-quality, sharp focus',
            'playful, cartoonish, vibrant colors',
            'elegant, sophisticated, soft pastel palette',
            'energetic, dynamic, motion blur effect',
            'minimalistic, clean, ample negative space',
            'dramatic, high contrast, chiaroscuro lighting',
            'cozy, warm, shallow depth of field', // New tone
        ];

        // --- 2. Use provided options or select a random fallback ---
        $style = $options['style'] ?? $defaultStyles[array_rand($defaultStyles)];
        $tone  = $options['tone']  ?? $defaultTones[array_rand($defaultTones)];
        
        // An optional negative prompt to guide the model away from unwanted elements
        $negativePrompt = $options['negativePrompt'] ?? 'ugly, deformed, blurry, low resolution, watermark, text, signature, words, letters, numbers, logo, screenshot, cartoon, out of frame';

        // --- 3. Dynamic Image Prompt Template (Enhanced) ---
        $imagePrompt = <<<EOT
    Generate a single, high-quality, aesthetically pleasing image based on the following content.

    Content for Visual Representation: "{$prompt}"

    Generation Parameters:
    - Art Style and Quality: {$style}
    - Vibe and Tone: {$tone}
    - Focus: Use creative visual metaphors, abstract concepts, or a detailed scene to tell the story of the content.
    - Negative Guidelines (AVOID): {$negativePrompt}
    - Crucial Constraint: DO NOT include any text, words, letters, numbers, logos, or watermarks.

    EOT;

        // --- 4. Configuration (Using more explicit checks) ---
        $generationConfig = [];

        // Aspect Ratio (e.g., '1:1', '16:9', '3:4')
        if (!empty($options['aspectRatio'])) {
            $generationConfig['aspectRatio'] = $options['aspectRatio'];
        }
        
        // Handle number of images
        if (!empty($options['count']) && (int)$options['count'] > 1) {
            $generationConfig['sampleCount'] = (int)$options['count'];
        }


        // --- 5. API Payload Construction ---
        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $imagePrompt]
                    ]
                ]
            ],
            // The API might expect generationConfig to be at the top level for image generation
            // Depending on your API client wrapper, this might be needed here or handled by the wrapper.
            // Assuming your `sendGenerateContentRequest` handles merging.
        ];

        try {
            // Assume $this->sendGenerateContentRequest handles the model and config correctly
            $body = $this->sendGenerateContentRequest($model, $payload, $generationConfig);

            // --- 6. Parsing the Response (Cleaner loop and error handling) ---
            $images = [];
            foreach ($body['candidates'] ?? [] as $candidate) {
                $part = $candidate['content']['parts'][0] ?? null; // Assume the image data is the first part
                
                if (!empty($part['inlineData']['data'])) {
                    $images[] = [
                        'b64_json' => $part['inlineData']['data'],
                        'mimeType' => $part['inlineData']['mimeType'] ?? 'image/png',
                    ];
                }
            }

            if (empty($images)) {
                // Check for potential error messages in the response if image data is missing
                $errorMessage = $body['candidates'][0]['finishReason'] ?? 'Unknown reason.';
                throw new \Exception("Model {$model} did not return image data. Finish reason: {$errorMessage}");
            }

            return $this->successResponse($model, $images);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, 'image');
        }
    }
    
    // --- Video Generation ---

    /**
     * Generates content based on a video file (multimodal analysis).
     */
    public function generateVideo(string $prompt, array $options = [], string $category = 'video'): array
    {
        $model = $this->getModel($category);

        $parts = [["text" => $prompt]];
        if (isset($options['video_uri'])) {
            $parts[] = [
                "fileData" => [
                    "mimeType" => $options['mimeType'] ?? "video/mp4",
                    "fileUri"  => $options['video_uri'],
                ]
            ];
        }

        $payload = ["contents" => [["parts" => $parts]]];
        
        try {
            $body = $this->sendGenerateContentRequest($model, $payload);
            // Process response body for video analysis/generation
            return $this->successResponse($model, $body);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    // --- Vision Generation ---

    /**
     * Generates content based on text and an image (Vision).
     */
    public function generateVision(string|array $prompt, array $options = [], string $category = 'vision'): array
    {
        $model = $this->getModel($category);

        $parts = array_filter([
            is_array($prompt) ? $prompt : ["text" => $prompt],
            isset($options['image_base64']) ? [
                "inlineData" => [
                    "mimeType" => $options['mimeType'] ?? "image/png",
                    "data"     => $options['image_base64'] // Base64 data already
                ]
            ] : null,
        ]);

        $payload = ["contents" => [["parts" => $parts]]];
        
        try {
            $body = $this->sendGenerateContentRequest($model, $payload);
            // Process response body for vision result
            return $this->successResponse($model, $body);

        } catch (\Throwable $e) {
            return $this->errorResponse($model, $e, $category);
        }
    }

    // --- Placeholder/Unsupported Functions ---

    public function generateEmbedding(string $text, array $options = [], string $category = 'embedding'): array
    {
        return $this->errorResponse('gemini-embedding', new \Exception("Gemini embedding not supported yet"), $category);
    }

    public function textToSpeech(string $text, array $options = [], string $category = 'speech'): array
    {
        return $this->errorResponse('gemini-tts', new \Exception("Gemini TTS not supported yet"), $category);
    }

    public function speechToText(string $filePath, array $options = [], string $category = 'speech_to_text'): array
    {
        return $this->errorResponse('gemini-stt', new \Exception("Gemini STT not supported yet"), $category);
    }

    public function generateAudio(string $filePath, array $options = [], string $category = 'audio'): array
    {
        return $this->speechToText($filePath, $options, $category);
    }

    // --- Response Helpers ---

    /**
     * Standard error response format.
     */
    protected function errorResponse(string $model, \Throwable $e, string $category = ''): array
    {
        Log::error("Gemini {$category} error with model {$model}: " . $e->getMessage());

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

    /**
     * Standard success response format.
     */
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
}