<?php
namespace Modules\AppMediaSearch\Services\Providers;

use Illuminate\Support\Facades\Http;
use Modules\AppMediaSearch\Services\Providers\ProviderInterface;

class UnsplashProvider implements ProviderInterface
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option("file_unsplash_access_key");
    }

    public function search(string $query, string $type = 'photo', int $page = 1): array
    {
        if ($type !== 'photo') return [];

        $response = Http::withHeaders([
            'Authorization' => 'Client-ID ' . $this->apiKey
        ])->get('https://api.unsplash.com/search/photos', [
            'query'    => $query,
            'per_page' => 30,
            'page'     => $page,
        ]);


        if ($response->failed()) return [];

        $results = $response->json('results');

        return collect($results)->map(function ($item) {
            return [
                'id'        => $item['id'],
                'thumbnail' => $item['urls']['thumb'],
                'full'      => $item['urls']['full'],
                'source'    => 'unsplash',
                'link'      => $item['links']['html'],
                'author'    => $item['user']['name'] ?? null,
            ];
        })->toArray();
    }
}