<?php

namespace Modules\AppMediaSearch\Services\Providers;

use Illuminate\Support\Facades\Http;
use Modules\AppMediaSearch\Services\Providers\ProviderInterface;

class PixabayVideoProvider implements ProviderInterface
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option("file_pixabay_api_key", "");
    }

    public function search(string $query, string $type = 'video', int $page = 1): array
    {
        if ($type !== 'video') return [];

        $response = Http::get('https://pixabay.com/api/videos/', [
            'key' => $this->apiKey,
            'q' => $query,
            'per_page' => 20,
            'page' => $page,
        ]);

        if ($response->failed()) return [];

        $results = $response->json('hits');

        return collect($results)->map(function ($item) {
            // Chọn chất lượng "medium", fallback "small"
            $videoFile = $item['videos']['medium'] ?? reset($item['videos']);
            $thumbnail = $videoFile['thumbnail'] ?? null;

            return [
                'id'        => $item['id'],
                'thumbnail' => $thumbnail, // Dùng thumbnail từ chính video
                'full'      => $videoFile['url'] ?? null,
                'source'    => 'pixabay',
                'link'      => $item['pageURL'],
                'author'    => $item['user'],
            ];
        })->toArray();
    }
}
