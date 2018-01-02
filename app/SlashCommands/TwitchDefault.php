<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\SlackService;
use App\Services\TwitchService;

class TwitchDefault implements SlashCommandsInterface
{
    use SlashCommandsTrait;

    private $command;
    /** @var SlackMember */
    private $operator;

    /** @var SlackService */
    private $slackService;

    public function __construct(array $command = [], SlackMember $operator = null)
    {
        $this->command = $command;
        $this->operator = $operator;
        $this->slackService = app(SlackService::class);
    }

    public function buildReply()
    {
        $fields = [
            [
                'title' => '/twitch list',
                'value' => '查看現在已經在追蹤的實況主名單',
            ],
            [
                'title' => '/twitch add <實況主頻道id>',
                'value' => implode(PHP_EOL, [
                    '新增追蹤實況主',
                    '舉例：小熊的實況網址為https://www.twitch.tv/yuniko0720',
                    '所以輸入：/twitch add yuniko0720',
                ]),
            ],
            [
                'title' => '/twitch delete <實況主頻道id>',
                'value' => implode(PHP_EOL, [
                    '刪除追蹤對象',
                    '舉例：小熊的實況網址為https://www.twitch.tv/yuniko0720',
                    '所以輸入：/twitch delete yuniko0720',
                ]),
            ],
        ];
        return $this->slackService->buildSlackMessages(
            'Twitch支援指令清單',
            '你可以輸入以下指令',
            $fields
        );
    }
}
