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
        $payload = $this->command['payload'];
        $currentPage = $payload['actions'][0]['value'] ?? 1;
        $perPage = 4;

        $collection = $this->comicService->getAll();
        $fields = $this->comicService->buildFollowList($collection, $currentPage, $perPage);
        $totalPage = $collection->chunk($perPage)->count();

        $this->slackService->buildSlackMessages(
            '漫畫追蹤名單',
            "目前共有 {$collection->count()} 篇漫畫追蹤中，當前第 {$currentPage} 頁，共 {$totalPage} 頁",
            $fields->toArray()
        );
        return $this->slackService->attachPage($currentPage, $totalPage, ComicService::class);
    }
}
