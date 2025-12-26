<?php
namespace App\Services;

use GuzzleHttp\Client;

class RebrandlyService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = get_option("rebrandly_api_key", "");
        $this->domain = get_option("rebrandly_domain", "rebrand.ly");
    }

    public function shorten($url)
    {
        if( get_option("rebrandly_status", 0) )
        {
            $response = $this->client->post('https://api.rebrandly.com/v1/links', [
                'headers' => [
                    'apikey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'destination' => $url,
                    'domain' => ['fullName' => $this->domain],
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['shortUrl'] ?? null;
        }
        else
        {
            return null;
        }
    }
}
