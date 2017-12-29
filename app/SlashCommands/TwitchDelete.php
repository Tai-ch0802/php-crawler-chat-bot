<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\SlackService;
use App\Services\TwitchService;

class TwitchDelete implements SlashCommandsInterface
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
        $fields = $this->twitchService->buildDeleteSubscription($channelName, $this->operator);
        if (null === $fields) {
            return $this->slackService->buildSlashCommandResponse(
                '本來就沒有追蹤這頻道！',
                '',
                [],
                SlackService::SLASH_COMMAND_REPLY_PRIVATE,
                SlackService::ATTACH_COLOR_RED
            );
        }

        return $this->slackService->buildSlashCommandResponse(
            '有人從追蹤名單裡被除名了！',
            '大家在看他最後一面吧！',
            $fields,
            SlackService::SLASH_COMMAND_REPLY_PUBLIC,
            SlackService::ATTACH_COLOR_ORANGE
        );
    }
}
