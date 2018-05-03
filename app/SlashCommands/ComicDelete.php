<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\ComicService;
use App\Services\SlackService;
use App\Services\TwitchService;

class ComicDelete implements SlashCommandsInterface
{
    use SlashCommandsTrait;

    private $command;
    /** @var SlackMember */
    private $operator;

    /** @var ComicService */
    private $comicService;
    /** @var SlackService */
    private $slackService;

    public function __construct(array $command = [], SlackMember $operator = null)
    {
        $this->command = $command;
        $this->operator = $operator;
        $this->comicService = app(ComicService::class);
        $this->slackService = app(SlackService::class);
    }

    public function buildReply()
    {
        $comicId = $this->command[1] ?? null;

        if (null === $comicId) {
            return $this->invalidTyping();
        }
        $fields = $this->comicService->buildDeleteSubscription($comicId, $this->operator);
        if (null === $fields) {
            return $this->slackService->buildSlackMessages(
                '本來就沒有追蹤這漫畫！',
                '',
                [],
                SlackService::SLASH_COMMAND_REPLY_PRIVATE,
                SlackService::ATTACH_COLOR_RED
            );
        }

        return $this->slackService->buildSlackMessages(
            '有人從追蹤名單裡被除名了！',
            '大家在看他最後一面吧！',
            $fields,
            SlackService::SLASH_COMMAND_REPLY_PUBLIC,
            SlackService::ATTACH_COLOR_ORANGE
        );
    }
}
