<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\SlackService;
use App\Services\TwitchService;

class ComicDefault implements SlashCommandsInterface
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
                'title' => '/comic list',
                'value' => '查看現在已經在追蹤的漫畫名單',
            ],
            [
                'title' => '/comic add <漫畫id>',
                'value' => implode(PHP_EOL, [
                    '新增追蹤漫畫',
                    '舉例：監獄學園的漫畫網址為http://99770.hhxxee.com/comic/7688/',
                    '所以輸入：/comic add 7688',
                ]),
            ],
            [
                'title' => '/comic delete <漫畫id>',
                'value' => implode(PHP_EOL, [
                    '刪除追蹤漫畫',
                    '舉例：監獄學園的漫畫網址為http://99770.hhxxee.com/comic/7688/',
                    '所以輸入：/comic delete 7688',
                ]),
            ],
        ];
        return $this->slackService->buildSlashCommandResponse(
            '漫畫支援指令清單',
            '你可以輸入以下指令',
            $fields
        );
    }
}
