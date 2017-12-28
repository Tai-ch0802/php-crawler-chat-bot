<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\SlackService;
use App\Services\TwitchService;

class TwitchDelete implements SlashCommandsInterface
{
    private $command;
    /** @var SlackMember */
    private $updater;

    /** @var TwitchService */
    private $twitchService;
    /** @var SlackService */
    private $slackService;

    public function __construct(array $command = [], SlackMember $updater)
    {
        $this->command = $command;
        $this->updater = $updater;
        $this->twitchService = app(TwitchService::class);
        $this->slackService = app(SlackService::class);
    }

    public function buildReply()
    {
        $channelName = $command[1] ?? null;

        //TODO use Trait
//        if (null === $channelName) {
//            break;
//        }
        $fields = $this->twitchService->buildDeleteSubscription($channelName, $this->updater);
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