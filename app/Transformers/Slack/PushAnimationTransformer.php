<?php

namespace App\Transformers\Slack;

use Yish\Generators\Foundation\Transform\TransformContract;

class PushAnimationTransformer implements TransformContract
{

    public function transform($data)
    {
        return [
            //TODO Author
            'author_name' => 'Test',
            'author_link' =>  'https://www.youtube.com/',
            'author_icon' =>  ':chicken01:',
            //TODO Title and link
            'title' => self::class,
            'title_link' => 'https://tai-service.slack.com/home',
            //TODO Info 雖然有點宅但這是堅持
            'text' => "`Testing`  ```This\nshit ``` \n ",
            //TODO Image 這也是種堅持
            'image_url' => 'http://img.94201314.net/comicui/170.jpg',
        ];
    }
}
