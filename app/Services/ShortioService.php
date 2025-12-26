<?php
namespace App\Services;

use GuzzleHttp\Client;

class ShortioService
{
    protected $client;
    protected $apiKey;
    protected $domain;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = get_option("shortio_api_key", "");
        $this->domain = get_option("shortio_domain", "");
    }

    public function shorten($url)
    {
        if( get_option("shortio_status", 0) ){
            if ( strpos($url, $this->domain) === false ) {
                $response = $this->client->post('https://api.short.io/links', [
                    'headers' => [
                        'Authorization' => $this->apiKey,
                        'Content-Type'  => 'application/json'
                    ],
                    'json' => [
                        'originalURL' => $url,
                        'domain'      => $this->domain,
                    ],
                ]);

                $data = json_decode($response->getBody(), true);
                return $data['shortURL'] ?? null;
            }

            return $url;
        }else{
            return null;
        }
    }
}
