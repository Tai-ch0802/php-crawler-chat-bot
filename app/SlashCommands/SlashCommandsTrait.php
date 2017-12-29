<?php
namespace App\SlashCommands;

use App\Services\SlackService;

trait SlashCommandsTrait
{
    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function noExistTarget()
    {
        return $this->slackService->buildSlashCommandResponse(
            '輸入對象代號不存在',
            "請確認新增對象 `{$this->command[1]}` 是否存在",
            [],
            SlackService::SLASH_COMMAND_REPLY_PUBLIC,
            SlackService::ATTACH_COLOR_RED
        );
    }
}
