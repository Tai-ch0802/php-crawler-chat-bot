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
        $payload = $this->command['payload'] ?? null;
        $perPage = 4;
        $currentPage = 1;
        $actionData = '';

        $this->getAttributesFromPayload($currentPage, $actionData, $payload);

        $collection = $this->twitchService->getAll();
        $fields = $this->twitchService->buildFollowList($collection, $currentPage, $perPage);
        $option = $this->twitchService->buildFilterOption($collection);
        $totalPage = $collection->chunk($perPage)->count();


        $this->slackService->buildSlackMessages(
            'Twitch追蹤名單',
            "目前共有 {$collection->count()} 位實況主追蹤中，當前第 {$currentPage} 頁，共 {$totalPage} 頁",
            $fields->toArray()
        );

        $this->slackService->attachPage($currentPage, $totalPage, TwitchService::class);
        return $this->slackService->attachFilter($option, $actionData, TwitchService::class);
    }

    private function getAttributesFromPayload(int &$currentPage, string &$actionData, $payload = null)
    {
        if (null === $payload) {
            return;
        }

        $actions = $payload['actions'];
        foreach ($actions as $action) {
            $actionName = $action['name'] ?? null;
            switch ($actionName) {
                case 'page':
                    $currentPage = $action['value'];
                    break;
                case 'filter':
                    $actionData = $action['selected_options'][0]['value'];
                    break;
            }
        }
    }
}
