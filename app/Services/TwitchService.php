<?php
namespace App\Services;

use App\Models\Twitch;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

class TwitchService
{
    /** @var Client  */
    private $client;
    private $url;

    public function __construct(Client $client, string $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    /**
     * @param string $channel
     * @return ResponseInterface
     */
    public function getLiveStreams(string $channel): ResponseInterface
    {
        return $this->client->get("/kraken/streams/?channel={$channel}");
    }

    /**
     * @return Collection
     */
    public function buildFollowList(): Collection
    {
        $collection = Twitch::all();

        $collection->transform(function (Twitch $item) {
            return [
                'title' => $item->name,
                'value' => implode(PHP_EOL, [
                    "實況網址： {$this->url}{$item->channel_name}",
                    "追蹤日期： {$item->created_at}",
                    "追蹤建立人： {$item->creator->user_name}",
                ]),
            ];
        });

        return $collection;
    }
}
