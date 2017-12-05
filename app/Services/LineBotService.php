<?php
namespace App\Services;

use LINE\LINEBot;

class LineBotService
{
    /** @var LINEBot */
    private $lineBot;
    private $lineUserId;

    public function __construct($lineUserId)
    {
        $this->lineUserId = $lineUserId;
        $this->lineBot = app(LINEBot::class);
    }

    /**
     * @param LINEBot\MessageBuilder\TemplateMessageBuilder|string $content
     * @return LINEBot\Response
     */
    public function pushMessage($content)
    {
        if(is_string($content)) {
            $content = new LINEBot\MessageBuilder\TextMessageBuilder($content);
        }
        return $this->lineBot->pushMessage($this->lineUserId, $content);
    }

    /**
     * @param $imagePath
     * @param $directUri
     * @param $label
     * @return LINEBot\MessageBuilder\TemplateMessageBuilder
     */
    public function getImageCarouselColumnTemplateBuilder($imagePath, $directUri, $label)
    {
        $target = new LINEBot\TemplateActionBuilder\UriTemplateActionBuilder($label, $directUri);
        $target->buildTemplateAction();

        $target =  new LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder($imagePath, $target);

        $target = new LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder([$target]);
//        dd($target->buildTemplate());

        return new LINEBot\MessageBuilder\TemplateMessageBuilder('test123', $target);
    }
}