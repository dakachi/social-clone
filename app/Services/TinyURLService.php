<?php
namespace App\Services;

use GuzzleHttp\Client;

class TinyURLService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function shorten($url)
    {
        if( get_option("tinyurl_status", 0) ){
            $response = $this->client->post('https://api.tinyurl.com/create', [
                'query' => [
                    'url' => $url,
                    'api_token' => get_option("tinyurl_api_key", "")
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['data']['tiny_url'] ?? null;
        }else{
            return null;
        }
    }
}
