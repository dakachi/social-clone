<?php
namespace App\Services;

use App\Services\BitlyService;
use App\Services\TinyURLService;
use App\Services\RebrandlyService;
use App\Services\ShortioService;

class URLShortenerService
{
    protected $BitlyService;
    protected $TinyURLService;
    protected $RebrandlyService;
    protected $ShortioService;

    public function __construct(
        BitlyService $BitlyService, 
        TinyURLService $TinyURLService, 
        RebrandlyService $RebrandlyService,
        ShortioService $ShortioService
    )
    {
        $this->BitlyService = $BitlyService;
        $this->TinyURLService = $TinyURLService;
        $this->RebrandlyService = $RebrandlyService;
        $this->ShortioService = $ShortioService;
    }

    /**
     * Shortens a given URL using the selected platform.
     *
     * @param string $url The URL to be shortened.
     * @param string $platform The shortening platform from the list: bitly, tinyurl, rebrandly, shortio.
     * @return string|null The shortened URL or null if shortening fails.
     */
    public function shorten($url, $platform = "")
    {
        try {
            if($platform == ""){
                $platform = get_option("url_shorteners_platform", "shortio");
            }

            switch ($platform) {
                case 'bitly':
                    return $this->BitlyService->shorten($url);
                case 'tinyurl':
                    return $this->TinyURLService->shorten($url);
                case 'rebrandly':
                    return $this->RebrandlyService->shorten($url);
                case 'shortio':
                    return $this->ShortioService->shorten($url);
                default:
                    throw new \Exception('URL shortening platform not supported');
            }
        } catch (\Exception $e) {
            return null;  
        }
    }

    /**
     * Returns a list of supported URL shortening platforms.
     *
     * @return array
     */
    public function getPlatforms()
    {
        return [
            "bitly"    => __("Bitly"),
            "tinyurl"  => __("TinyURL"),
            "rebrandly"=> __("Rebrandly"),
            "shortio"  => __("Short.io"),
        ];
    }

    /**
     * Finds all URLs in the given content, shortens them using the specified platform,
     * and replaces the original URLs with their shortened versions.
     *
     * @param string $content The content containing one or more URLs.
     * @param string $platform The URL shortening platform to use (default is 'bitly').
     * @return string The content with the original URLs replaced by their shortened versions.
     */
    public function shortenUrlsInContent($content, $platform = "")
    {
        try {
            // Regular expression to match URLs starting with http:// or https://
            $pattern = '/https?:\/\/[^\s"]+/i';

            // Find all URLs within the content
            preg_match_all($pattern, $content, $matches);

            if (!empty($matches[0])) {
                foreach ($matches[0] as $url) {
                    try {
                        // Shorten the URL using the chosen platform
                        $shortUrl = $this->shorten($url, $platform);
                        if ($shortUrl) {
                            // Replace the original URL with the shortened URL in the content
                            $content = str_replace($url, $shortUrl, $content);
                        }
                    } catch (\Exception $e) {
                        // If an error occurs while shortening, skip this URL and continue
                        continue;
                    }
                }
            }
            return $content;
        } catch (\Exception $e) {
            return $content;
        }
    }
}
