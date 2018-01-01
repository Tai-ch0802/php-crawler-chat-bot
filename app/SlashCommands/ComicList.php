<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\ComicService;
use App\Services\SlackService;
use App\Services\TwitchService;

class ComicList implements SlashCommandsInterface
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
        $fields = $this->comicService->buildFollowList();
        return $this->slackService->buildSlashCommandResponse(
            '漫畫追蹤名單',
            "目前共有 {$fields->count()} 篇漫畫追蹤中",
            $fields->toArray()
        );
    }
}
