<?php
namespace App\SlashCommands;

use App\Services\SlackService;

trait SlashCommandsTrait
{
    public function invalidTyping()
    {
        return $this->slackService->buildSlashCommandResponse(
            '輸入指令錯誤',
            "請確認 `{$this->command[0]}` 指令格式",
            [],
            SlackService::SLASH_COMMAND_REPLY_PRIVATE,
            SlackService::ATTACH_COLOR_RED
        );
    }
}
