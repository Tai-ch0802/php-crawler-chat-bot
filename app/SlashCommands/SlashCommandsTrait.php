<?php
namespace App\SlashCommands;


use App\Services\SlackService;

trait SlashCommandsTrait
{
    public function invalidTyping()
    {
        return $this->slackService->buildSlashCommandResponse(
            'Twitch輸入指令錯誤',
            "請輸入 `{$this->command[0]}` 確認指令格式",
            [],
            SlackService::SLASH_COMMAND_REPLY_PRIVATE,
            SlackService::ATTACH_COLOR_RED
        );
    }
}