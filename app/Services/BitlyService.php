<?php
namespace App\Services;

use GuzzleHttp\Client;

class BitlyService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = get_option("bitly_api_key", "");
    }

    public function shorten($url)
    {
        if( get_option("bitly_status", 0) )
        {
            $response = $this->client->post('https://api-ssl.bitly.com/v4/shorten', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'long_url' => $url,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['link'] ?? null;
        }
        else
        {
            return null;
        }
        
    }
}
