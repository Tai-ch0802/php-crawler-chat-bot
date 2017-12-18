<?php

namespace App\Transformers\Slack;

use Yish\Generators\Foundation\Transform\TransformContract;

class PushAnimationTransformer implements TransformContract
{

    public function transform($data)
    {
        return [
            'author_name' => $data['authorName'],
            'author_link' =>  $data['authorLink'],
            'author_icon' =>  $data['authorIcon'],
            'title' => $data['label'],
            'title_link' => $data['directUri'],
            'text' => $data['text'],
            'image_url' => $data['imagePath'],
        ];
    }
}
