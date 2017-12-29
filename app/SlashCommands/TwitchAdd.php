<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\SlackService;
use App\Services\TwitchService;

class TwitchAdd implements SlashCommandsInterface
{
    use SlashCommandsTrait;

    private $command;
    /** @var SlackMember */
    private $operator;

    /** @var TwitchService */
    private $twitchService;
    /** @var SlackService */
    private $slackService;

    public function __construct(array $command = [], SlackMember $operator = null)
    {
        $this->command = $command;
        $this->operator = $operator;
        $this->twitchService = app(TwitchService::class);
        $this->slackService = app(SlackService::class);
    }

    public function buildReply()
    {
        $channelName = $this->command[1] ?? null;

        if (null === $channelName) {
            return $this->invalidTyping();
        }

        $fields = $this->twitchService->buildNewSubscription($channelName, $this->operator);
        if (null === $fields) {
            return $this->noExistTarget();
        }

        return $this->slackService->buildSlashCommandResponse(
            '有新實況主納入追蹤名單！',
            '請參考以下資訊',
            $fields,
            SlackService::SLASH_COMMAND_REPLY_PUBLIC,
            SlackService::ATTACH_COLOR_GREEN
        );
    }
}
