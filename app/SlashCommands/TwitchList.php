<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\SlackService;
use App\Services\TwitchService;

class TwitchList implements SlashCommandsInterface
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
        $payload = $this->command['payload'];
        $currentPage = $payload['page'] ?? 1;
        $perPage = 4;

        $collection = $this->twitchService->getAll();
        $fields = $this->twitchService->buildFollowList($collection, $currentPage, $perPage);
        $totalPage = $collection->chunk($perPage)->count();


        $this->slackService->buildSlackMessages(
            'Twitch追蹤名單',
            "目前共有 {$collection->count()} 位實況主追蹤中，當前第 {$currentPage} 頁，共 {$totalPage} 頁",
            $fields->toArray()
        );
        return $this->slackService->attachPage($currentPage, $totalPage);
    }
}
