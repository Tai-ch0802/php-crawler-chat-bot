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
        $presenterName = $this->command[1] ?? null;
        $channelName = $this->command[2] ?? null;

        if (in_array(
            null,
            [
                $presenterName,
                $channelName
            ],
            true
        )) {
            return $this->invalidTyping();
        }


        $fields = $this->twitchService->buildNewSubscription($presenterName, $channelName, $this->updater);
        return $this->slackService->buildSlashCommandResponse(
            '有新實況主納入追蹤名單！',
            '請參考以下資訊',
            $fields,
            SlackService::SLASH_COMMAND_REPLY_PUBLIC,
            SlackService::ATTACH_COLOR_GREEN
        );
    }
}