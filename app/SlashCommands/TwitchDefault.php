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
    private $updater;

    /** @var SlackService */
    private $slackService;

    public function __construct(array $command = [], SlackMember $updater)
    {
        $this->command = $command;
        $this->updater = $updater;
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
                'title' => '/twitch add <實況主名稱> <實況主頻道id>',
                'value' => "新增追蹤實況主\n舉例：/twitch add 小熊 yuniko0720",
            ],
            [
                'title' => '/twitch delete <實況主頻道id>',
                'value' => "刪除追蹤對象\n舉例：/twitch delete yuniko0720",
            ],
        ];
        return $this->slackService->buildSlashCommandResponse(
            'Twitch支援指令清單',
            '你可以輸入以下指令',
            $fields
        );
    }
}