<?php

namespace Modules\AppMediaSearch\Services\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Modules\AppMediaSearch\Services\Providers\ProviderInterface;

class PexelsVideoProvider implements ProviderInterface
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) get_option('file_pexels_api_key', '');
    }

    public function search(string $query, string $type = 'video', int $page = 1): array
    {
        $query = trim($query);
        $page  = max(1, (int) $page);

        if ($type !== 'video' || $query === '' || $this->apiKey === '') {
            return [];
        }

        try {
            $response = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->retry(2, 200)
                ->timeout(10)
                ->get('https://api.pexels.com/videos/search', [
                    'query'    => $query,
                    'per_page' => 20,
                    'page'     => $page,
                ]);

            if ($response->failed()) {
                return [];
            }

            $results = (array) $response->json('videos', []);
        } catch (RequestException $e) {
            return [];
        } catch (\Throwable $e) {
            return [];
        }

        return collect($results)
            ->map(function ($item) {
                $thumb = data_get($item, 'image');
                $files = (array) data_get($item, 'video_files', []);

                if (!$thumb || empty($files)) {
                    return null;
                }

                $best = collect($files)
                    ->filter(fn ($f) => isset($f['link']) && isset($f['width']))
                    ->sortByDesc('width')
                    ->first() ?? $files[0];

                $full = (string) data_get($best, 'link');

                if ($full === '') {
                    return null;
                }

                return [
                    'id'        => (int) data_get($item, 'id'),
                    'thumbnail' => (string) $thumb,
                    'full'      => $full,
                    'source'    => 'pexels',
                    'link'      => (string) data_get($item, 'url', ''),
                    'author'    => (string) data_get($item, 'user.name', ''),
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
