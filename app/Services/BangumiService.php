<?php
namespace App\Services;


use GuzzleHttp\Client;

class BangumiService
{
    /**
     * @var Client
     */
    private $client;

    private $endpoint;

    public function __construct($endpoint, $id, $secret)
    {
        $this->endpoint = $endpoint;
        $this->client = new Client();
    }



    /**
     * 取得每日放送節目表
     *
     * @return array
     */
    public function getCalendar(): array
    {
        $response = $this->client->get($this->endpoint . '/calendar');

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }

    /**
     * 取得指定番的全集
     *
     * @param int $subjectId
     * @return array
     */
    public function getSubjectEps(int $subjectId): array
    {
        $response = $this->client->get($this->endpoint . "/subject/{$subjectId}/ep");

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }
}
