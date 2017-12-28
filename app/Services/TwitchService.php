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

    /**
     * @param string $text
     * @param SlackMember $updater
     * @return array
     * @throws \Exception
     */
    public function replySlashCommand(string $text, SlackMember $updater)
    {
        /** @var SlackService $slackService */
        $slackService = app(SlackService::class);

        $data = explode(' ', $text);
        $command = $data[0];
        $response = [];
        switch ($command) {
            case 'list':
                //TODO show list
                $fields = $this->buildFollowList();
                $response = $slackService->buildSlashCommandResponse(
                    'Twitch追蹤名單',
                    "目前共有 {$fields->count()} 位實況主追蹤中",
                    $fields->toArray()
                );
                break;

            case 'add':
                //TODO add new item, example: /twitch add <name> <channelName>
                $presenterName = $data[1] ?? null;
                $channelName = $data[2] ?? null;
                if (in_array(
                    null,
                    [
                        $presenterName,
                        $channelName
                    ],
                    true
                )) {
                    break;
                }
                $fields = $this->buildNewSubscription($presenterName, $channelName, $updater);
                $response = $slackService->buildSlashCommandResponse(
                    '有新實況主納入追蹤名單！',
                    '請參考以下資訊',
                    $fields,
                    SlackService::SLASH_COMMAND_REPLY_PUBLIC,
                    SlackService::ATTACH_COLOR_GREEN
                );
                break;

            case 'delete':
                //TODO add new item, example: /twitch delete <channelName>
                //TODO add permission(?)
                $channelName = $data[1] ?? null;
                if (null === $channelName) {
                    break;
                }
                $fields = $this->buildDeleteSubscription($channelName, $updater);
                if (null === $fields) {
                    $response = $slackService->buildSlashCommandResponse(
                        '本來就沒有追蹤這頻道！',
                        '',
                        [],
                        SlackService::SLASH_COMMAND_REPLY_PRIVATE,
                        SlackService::ATTACH_COLOR_RED
                    );
                    break;
                }

                $response = $slackService->buildSlashCommandResponse(
                    '有人從追蹤名單裡被除名了！',
                    '大家在看他最後一面吧！',
                    $fields,
                    SlackService::SLASH_COMMAND_REPLY_PUBLIC,
                    SlackService::ATTACH_COLOR_ORANGE
                );
                break;

            default:
                $fields = [
                    [
                        'title' => '/twitch list',
                        'value' => '查看現在已經在追蹤的實況主名單',
                    ],
                    [
                        'title' => '/twitch add <實況主名稱> <實況主頻道id>',
                        'value' => "新增追蹤實況主\n舉例：/twitch add 小熊 yuniko0720",
                    ],
                    [
                        'title' => '/twitch delete <實況主頻道id>',
                        'value' => "刪除追蹤對象\n舉例：/twitch delete yuniko0720",
                    ],
                ];
                $response = $slackService->buildSlashCommandResponse(
                    'Twitch支援指令清單',
                    '你可以輸入以下指令',
                    $fields
                );
        }

        if (empty($response)) {
            $response = $slackService->buildSlashCommandResponse(
                'Twitch輸入指令錯誤',
                '請輸入 `/twitch` 確認指令格式',
                [],
                SlackService::SLASH_COMMAND_REPLY_PRIVATE,
                SlackService::ATTACH_COLOR_RED
            );
        }

        return $response;
    }
}
