<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\ComicService;
use App\Services\SlackService;

class ComicAdd implements SlashCommandsInterface
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

        $fields = $this->comicService->buildNewSubscription($comicId, $this->operator);
        if (null === $fields) {
            return $this->noExistTarget();
        }

        return $this->slackService->buildSlashCommandResponse(
            '有新漫畫納入追蹤名單！',
            '請參考以下資訊',
            $fields,
            SlackService::SLASH_COMMAND_REPLY_PUBLIC,
            SlackService::ATTACH_COLOR_GREEN
        );
    }
}
