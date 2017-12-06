<?php
namespace App\Services;

use LINE\LINEBot;
use LINE\LINEBot\Response;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;

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

    public function fake()
    {
    }

    /**
     * @param TemplateMessageBuilder|string $content
     * @return Response
     */
    public function pushMessage($content): Response
    {
        if (is_string($content)) {
            $content = new TextMessageBuilder($content);
        }
        return $this->lineBot->pushMessage($this->lineUserId, $content);
    }

    /**
     * @param string $imagePath
     * @param string $directUri
     * @param string $label
     * @return TemplateMessageBuilder
     */
    public function getImageCarouselColumnTemplateBuilder(
        string $imagePath,
        string $directUri,
        string $label
    ): TemplateMessageBuilder {
        $target = new UriTemplateActionBuilder($label, $directUri);
        $target =  new ImageCarouselColumnTemplateBuilder($imagePath, $target);
        $target = new ImageCarouselTemplateBuilder([$target]);

        return new TemplateMessageBuilder('test123', $target);
    }
}
