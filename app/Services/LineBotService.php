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

    public function pushMessage(string $string)
    {
        $messageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder($string);

        return $this->lineBot->pushMessage($this->lineUserId, $messageBuilder);
    }
}