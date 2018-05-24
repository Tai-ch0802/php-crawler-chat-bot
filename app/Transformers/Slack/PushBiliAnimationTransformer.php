<?php
namespace App\Transformers\Slack;

use App\Transformers\TransformerInterface;

class PushBiliAnimationTransformer implements TransformerInterface
{
    public function transform($data)
    {
        return [
            'author_name' => 'Bangumi',
            'author_link' =>  'http://bgm.tv/subject',
            'author_icon' =>  null,
            'title' => "{$data['name']} 第{$data['ep']['sort']}集 上線啦！",
            'title_link' => $data['ep']['url'],
            'text' => $data['ep']['desc'],
            'image_url' => $data['image'],
        ];
    }
}
