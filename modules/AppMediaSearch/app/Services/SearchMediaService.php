<?php
namespace Modules\AppMediaSearch\Services;

use Modules\AppMediaSearch\Services\Providers\ProviderInterface;

class SearchMediaService
{
    protected array $providers = [];

    public function registerProvider(string $name, ProviderInterface $provider)
    {
        $this->providers[$name] = $provider;
    }

    public function services()
    {
        $services = [];

        if (get_option("file_unsplash_status", 0) == 1) {
            $services['unsplash'] = __('Unsplash');
        }

        if (get_option("file_pexels_status", 0) == 1) {
            $services['pexels_photo'] = __('Pexels Photo');
            $services['pexels_video'] = __('Pexels Video');
        }

        if (get_option("file_pixabay_status", 0) == 1) {
            $services['pixabay_photo'] = __('Pixabay Photo');
            $services['pixabay_video'] = __('Pixabay Video');
        }

        return $services;
    }

    public function search(string $query, string $type = 'photo', string $provider = 'unsplash'): array
    {
        if (!isset($this->providers[$provider])) {
            throw new \Exception("Provider [$provider] not registered.");
        }

        return $this->providers[$provider]->search($query, $type);
    }

    public function allProviders(): array
    {
        return array_keys($this->providers);
    }

    public function find(string $query, string $provider = 'unsplash'): array
    {
        if($provider == "pexels_video" || $provider == "pixabay_video"){
            return $this->searchVideo($query, $provider);
        }else{
            return $this->searchImage($query, $provider);
        }
        
    }

    public function searchImage(string $query, string $provider = 'unsplash'): array
    {
        return $this->search($query, 'photo', $provider);
    }

    public function searchVideo(string $query, string $provider = 'pexels_video'): array
    {
        return $this->search($query, 'video', $provider);
    }
}
