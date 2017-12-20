<?php
namespace App\Transformers\Slack;

class PushTwitchTransformer
{
    public static function transform($data)
    {
        return [
            'author_name' => $data['authorName'],
            'author_icon' =>  $data['authorIcon'],
            'title' => $data['label'],
            'title_link' => $data['directUri'],
            'text' => $data['text'],
            'image_url' => $data['imagePath'],
        ];
    }
}
