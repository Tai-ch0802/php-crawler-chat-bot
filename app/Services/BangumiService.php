<?php
namespace App\Services;


use GuzzleHttp\Client;

class BangumiService
{
    private $token;

    public function __construct($id, $secret)
    {

    }

    private function buildToken($id)
    {
        $client = new Client();

        $data = [
            'client_id' => $id,
            'response_type' => 'code',
            'redirect_uri' => 'https://php-crawler-chat-bot.herokuapp.com/',
        ];

        $response = $client->get('https://bgm.tv/oauth/authorize', $data);

        dd($response);
    }
}
