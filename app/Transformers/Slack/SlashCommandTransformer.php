<?php
namespace App\Transformers\Slack;

use App\Services\SlackService;

class SlashCommandTransformer
{
    public static function transform($data)
    {
        return [
            'response_type' => $data['responseType'] ?? SlackService::SLASH_COMMAND_REPLY_PRIVATE,
            'text' => $data['text'] ?? '',
            'attachments' => [
                'title' => $data['title'],
                'text' => $data['content'],
                'color' => $data['color'] ?? SlackService::ATTACH_COLOR_BLUE,
            ],
        ];
    }
}
