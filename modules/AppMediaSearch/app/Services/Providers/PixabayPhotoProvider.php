<?php

namespace Modules\AppMediaSearch\Services\Providers;

use Illuminate\Support\Facades\Http;
use Modules\AppMediaSearch\Services\Providers\ProviderInterface;

class PixabayPhotoProvider implements ProviderInterface
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option("file_pixabay_api_key", "");
    }

    public function search(string $query, string $type = 'photo', int $page = 1): array
    {
        if ($type !== 'photo') return [];

        $response = Http::get('https://pixabay.com/api/', [
            'key' => $this->apiKey,
            'q' => $query,
            'image_type' => 'photo',
            'per_page' => 20,
            'page' => $page,
        ]);

        if ($response->failed()) return [];

        $results = $response->json('hits');

        return collect($results)->map(function ($item) {
            return [
                'id'        => $item['id'],
                'thumbnail' => $item['previewURL'],
                'full'      => $item['largeImageURL'],
                'source'    => 'pixabay',
                'link'      => $item['pageURL'],
                'author'    => $item['user'],
            ];
        })->toArray();
    }
}
