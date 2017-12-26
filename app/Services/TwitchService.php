<?php
namespace App\Services;

use App\Models\SlackMember;
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
        //FIXME use repository(?)
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

    /**
     * @param string $presenterName
     * @param string $channelName
     * @param SlackMember $updater
     * @return array
     */
    public function buildNewSubscription(string $presenterName, string $channelName, SlackMember $updater): array
    {
        //TODO verify the endpoint

        //FIXME use repository(?)
        $twitch = new Twitch();
        $twitch->channel_name = $channelName;
        $twitch->name = $presenterName;
        $twitch->created_by = $updater->id;
        $twitch->updated_by = $updater->id;
        $twitch->save();

        return [
            [
                'title' => $twitch->name,
                'value' => implode(PHP_EOL, [
                    "實況網址： {$this->url}{$twitch->channel_name}",
                    "追蹤日期： {$twitch->created_at}",
                    "追蹤建立人： {$twitch->creator->user_name}",
                ]),
            ],
        ];
    }

    /**
     * @param string $channelName
     * @param SlackMember $updater
     * @return null|array
     * @throws \Exception
     */
    public function buildDeleteSubscription(string $channelName, SlackMember $updater): ?array
    {
        //FIXME use repository(?)
        /** @var Twitch $twitch */
        $twitch = Twitch::where('channel_name', $channelName)->first();
        if (null === $twitch) {
            return null;
        }

        $data = [
            [
                'title' => $twitch->name,
                'value' => implode(PHP_EOL, [
                    "實況網址： {$this->url}{$twitch->channel_name}",
                    "追蹤日期： {$twitch->created_at}",
                    "追蹤建立人： {$twitch->creator->user_name}",
                    "刪除操作者： {$updater->user_name}",
                ]),
            ],
        ];

        $twitch->delete();

        return $data;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll()
    {
        //FIXME use repository(?)
        return Twitch::all();
    }
}
