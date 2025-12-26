<?php

namespace App\Services;

use GuzzleHttp\Client;

class DeepSpeekService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.deepseek.com/v1/']);
        $this->apiKey = get_option("ai_deepseek_api_key", "");
    }

    public function getModels()
    {
        try {
            $response = $this->client->request('GET', 'models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
            ]);
            $models = json_decode($response->getBody(), true);

            $arr = [];
            if (isset($models['data'])) {
                foreach ($models['data'] as $model) {
                    $arr[$model['id']] = $model['description'] ?? $model['id'];
                }
                return $arr;
            }
        } catch (\Throwable $e) {}

        return false;
    }

    public function generateText($prompt, $maxLength = 1000, $maxResult = 1)
    {
        $model = get_option("ai_deepseek_model", "deepseek-v3");

        $messages = is_array($prompt)
            ? $prompt
            : [[
                "role" => "user",
                "content" => $prompt,
            ]];

        try {
            $response = $this->client->request('POST', 'chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'    => $model,
                    'messages' => $messages,
                    'max_tokens' => (int)$maxLength,
                    'n'         => $maxResult,
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

            return [
                "data" => $result,
                "promptTokens" => $body['usage']['prompt_tokens'] ?? 0,
                "completionTokens" => $body['usage']['completion_tokens'] ?? 0,
                "totalTokens" => $body['usage']['total_tokens'] ?? 0,
                "model" => $model
            ];

        } catch (\Throwable $e) {
            return [
                "data" => [],
                "promptTokens" => 0,
                "completionTokens" => 0,
                "totalTokens" => 0,
                "model" => $model,
                "error" => $e->getMessage()
            ];
        }
    }
}
