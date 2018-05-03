<?php
namespace App\SlashCommands;

use App\Models\SlackMember;
use App\Services\ComicService;
use App\Services\SlackService;

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
        $payload = $this->command['payload'] ?? null;
        $perPage = 4;
        $currentPage = 1;
        $actionData = '';

        $this->getAttributesFromPayload($currentPage, $actionData, $payload);

        $collection = $this->comicService->getAll();
        $fields = $this->comicService->buildFollowList($collection, $currentPage, $perPage);
        $option = $this->comicService->buildFilterOption($collection);
        $totalPage = $collection->chunk($perPage)->count();

        $this->slackService->buildSlackMessages(
            '漫畫追蹤名單',
            "目前共有 {$collection->count()} 篇漫畫追蹤中，當前第 {$currentPage} 頁，共 {$totalPage} 頁",
            $fields->toArray()
        );
        $this->slackService->attachPage($currentPage, $totalPage, ComicService::class);
        return $this->slackService->attachFilter($option, $actionData, ComicService::class);
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
