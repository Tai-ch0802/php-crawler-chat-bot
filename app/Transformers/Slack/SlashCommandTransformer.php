<?php
namespace App\Transformers\Slack;

class SlashCommandTransformer
{
    public static function transform($data)
    {
        return [
            'response_type' => $data['responseType'],
            'text' => $data['text'],
            'attachments' => [
                'text' => $data['text'],
                'color' => $data['color'],
            ],
            'title' => $data['label'],
            'title_link' => $data['directUri'],
            'text' => $data['text'],
            'image_url' => $data['imagePath'],
        ];
    }
}
