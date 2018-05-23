<?php
namespace App\Services;


use GuzzleHttp\Client;

class BangumiService
{
    private $token;

    public function __construct($id, $secret)
    {

    }



    /**
     * 取得每日放送節目表
     *
     * @return array
     */
    public function getCalendar(): array
    {
        $client = new Client();

        $response = $client->get('https://api.bgm.tv/calendar');

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }
}
